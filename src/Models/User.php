<?php
// src/Models/User.php

namespace Models;

use Core\Database;
use PDO;
use Core\Config;

class User
{
    private static function getDb()
    {
        return Database::getInstance()->getConnection();
    }


    public function getAllUsers() {
        $db = self::getDb();
        $stmt = $db->query("SELECT member_id, first_name, last_name, email, status FROM MEMBER");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAll()
    {
        $db = self::getDb();
        return $db->query("SELECT * FROM MEMBER");

    }

    public function find($memberId) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM MEMBER WHERE member_id = ?");
        $stmt->execute([$memberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function login($email, $password)
    {
        $db = self::getDb();
        $query = "SELECT * FROM MEMBER WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public static function getUserByEmail($email)
    {
        $db = self::getDb();
        $query = "SELECT * FROM MEMBER WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getUserById($memberId)
    {
        $db = self::getDb();
        $query = "SELECT * FROM MEMBER WHERE member_id = :memberId";
        $stmt = $db->prepare($query);
        $stmt->execute(['memberId' => $memberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateUser($memberId, $data)
    {
        $db = self::getDb();
        $allowedFields = ['last_name', 'first_name', 'email', 'birth_date', 'address', 'phone'];
        $setFields = [];
        $params = ['memberId' => $memberId];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $setFields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        if (empty($setFields)) {
            return false;
        }

        $setClause = implode(', ', $setFields);
        $query = "UPDATE MEMBER SET $setClause WHERE member_id = :memberId";
        $stmt = $db->prepare($query);
        return $stmt->execute($params);
    }

    public static function deleteUser($memberId)
    {
        $db = self::getDb();
        $query = "DELETE FROM MEMBER WHERE member_id = :memberId";
        $stmt = $db->prepare($query);
        return $stmt->execute(['memberId' => $memberId]);
    }

    public static function getSubscription($memberId)
    {
        $db = self::getDb();
        $query = "SELECT * FROM SUBSCRIPTION WHERE member_id = :memberId AND status = 'Active'";
        $stmt = $db->prepare($query);
        $stmt->execute(['memberId' => $memberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function addSubscription($memberId, $subscriptionType, $startDate, $endDate, $amount)
    {
        $db = self::getDb();
        $query = "INSERT INTO SUBSCRIPTION (member_id, subscription_type, start_date, end_date, amount) 
                  VALUES (:memberId, :subscriptionType, :startDate, :endDate, :amount)";
        $stmt = $db->prepare($query);
        return $stmt->execute([
            'memberId' => $memberId,
            'subscriptionType' => $subscriptionType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'amount' => $amount
        ]);
    }

    public static function findByEmail($email)
    {
        $db = self::getDb();
        $query = "SELECT * FROM MEMBER WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($userData)
    {
        $db = self::getDb();
        $verificationToken = bin2hex(random_bytes(32));
        $password = $userData['password'] ?? 'GOOGLE_USER';
        $userData['password'] = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO MEMBER (email, password, first_name, last_name, birth_date, address, phone, verification_token, is_verified)
                  VALUES (:email, :password, :first_name, :last_name, :birth_date, :address, :phone, :verification_token, FALSE)";

        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            'email' => $userData['email'],
            'password' => $password,
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'birth_date' => $userData['birth_date'],
            'address' => $userData['address'],
            'phone' => $userData['phone'],
            'verification_token' => $verificationToken
        ]);

        if ($result) {
            self::sendVerificationEmail($userData['email'], $verificationToken); // todo if we couldn't send email, delete from the database.
            return $db->lastInsertId();
        }
        return false;
    }

    private static function sendVerificationEmail($to, $token)
    {
        $mail_parts = Config::get("mail_parts");

        $verify_url = Config::get("server_url" . "/verify-mail?token=" . $token);
        $title = "Vérifiez votre adresse mail - " . Config::get("brand", "Sportify");

        $mail_parts['mail_body'] = str_replace("[TITLE]", $title, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[PARAGRAPH]", "Merci de cliquer sur le lien ci-dessous pour vérifier votre adresse email :", $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[VERIFY_URL]", Config::get("server_url") . $verify_url, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[ANCHOR]", "Vérifier mon mail",$mail_parts['mail_body']);

        $subject = $title;

        $message =  $mail_parts['mail_head'] . 
                    $mail_parts['mail_title'] . 
                    $mail_parts['mail_head_end'] .
                    $mail_parts['mail_body'] .
                    $mail_parts['mail_footer'];

        $headers = "From: sportify@alwaysdata.net\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if(mail($to, $subject, $message, $headers)) {
            return true;
        } else {
            error_log("Erreur d'envoi d'email à $to: " . error_get_last());
            return false;
        }
    }
  
    public static function updatePassword($userId, $newPassword)
    {
        $db = self::getDb();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE MEMBER SET password = :password WHERE member_id = :userId";
        $stmt = $db->prepare($query);
        return $stmt->execute([
            'password' => $hashedPassword,
            'userId' => $userId
        ]);
    }


    // Méthode pour mettre à jour le profil utilisateur
    public static function updateUserProfile($userId, $data)
    {
        $db = self::getDb();

        // Champs autorisés à être mis à jour
        $allowedFields = ['first_name', 'last_name', 'email', 'birth_date', 'address', 'phone'];
        $setFields = [];
        $params = ['userId' => $userId];

        // Ajouter les champs autorisés à la requête SQL
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $setFields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        // Si un nouveau mot de passe est renseigné
        if (!empty($data['new_password'])) {
            $setFields[] = "password = :password";
            $params['password'] = password_hash($data['new_password'], PASSWORD_BCRYPT);
        }

        // Si aucun champ n'est renseigné (cas improbable)
        if (empty($setFields)) {
            return false;
        }

        // Préparation de la requête SQL pour la mise à jour
        $setClause = implode(', ', $setFields);
        $query = "UPDATE MEMBER SET $setClause WHERE member_id = :userId";
        $stmt = $db->prepare($query);

        // Exécution de la requête
        return $stmt->execute($params);
    }


    // Méthode pour vérifier le mot de passe actuel de l'utilisateur
    public static function verifyCurrentPassword($userId, $currentPassword)
    {
        $db = self::getDb();
        $query = "SELECT password FROM MEMBER WHERE member_id = :userId";
        $stmt = $db->prepare($query);
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user && password_verify($currentPassword, $user['password']);
    }



    public static function verifyEmail($token)
    {
        $db = self::getDb();
        $query = "UPDATE MEMBER SET is_verified = TRUE, verification_token = NULL WHERE verification_token = :token";
        $stmt = $db->prepare($query);
        return $stmt->execute(['token' => $token]);
    }

    public function getGoogleUserByEmail($email)
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM MEMBRE WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function registerGoogleUser($userData)
    {
        $db = self::getDb();
        $stmt = $db->prepare("INSERT INTO MEMBRE (email, first_name, last_name, google_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $userData['email'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['google_id']
        ]);
    }

    public static function storeResetToken($email, $token)
    {
        $db = self::getDb();
        $query = "UPDATE MEMBER SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email";
        $stmt = $db->prepare($query);
        return $stmt->execute(['token' => $token, 'email' => $email]);
    }

    public static function findByResetToken($token)
    {
        $db = self::getDb();
        $query = "SELECT * FROM MEMBER WHERE reset_token = :token AND reset_token_expiry > NOW()";
        $stmt = $db->prepare($query);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function resetPassword($token, $newPassword)
    {
        $db = self::getDb();
        $user = self::findByResetToken($token);

        if ($user) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $query = "UPDATE MEMBER SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE member_id = :userId";
            $stmt = $db->prepare($query);
            return $stmt->execute(['password' => $hashedPassword, 'userId' => $user['member_id']]);
        }

        return false;
    }

    public static function sendPasswordResetEmail($email, $token)
    {
        $mail_parts = Config::get("mail_parts");

        $verify_url = Config::get("server_url" . "/reset-password?token=" . $token);
        $title = "Réinitialisation de votre mot de passe " . Config::get("brand", "Sportify");

        $mail_parts['mail_body'] = str_replace("[TITLE]", $title, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[PARAGRAPH]", "Merci de cliquer sur ce lien pour réinitialiser votre mot de passe : ", $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[VERIFY_URL]", Config::get("server_url") . $verify_url, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[ANCHOR]", "Changer mon mot de passe",$mail_parts['mail_body']);

        $subject = $title;

        $message =  $mail_parts['mail_head'] . 
                    $mail_parts['mail_title'] . 
                    $mail_parts['mail_head_end'] .
                    $mail_parts['mail_body'] .
                    $mail_parts['mail_footer'];

        $headers = "From: sportify@alwaysdata.net\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if(mail($email, $subject, $message, $headers)) {
            return true;
        } else {
            error_log("Erreur d'envoi d'email de réinitialisation à $email: " . error_get_last());
            return false;
        }
    }

}