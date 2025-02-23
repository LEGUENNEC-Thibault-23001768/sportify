<?php
// src/Models/User.php

namespace Models;

use Core\Config;
use Core\Database;
use PDO;

class User
{
    public static function getAllUsers()
    {
        $sql = "SELECT member_id, first_name, last_name, email, status FROM MEMBER";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAll()
    {
        $sql = "SELECT * FROM MEMBER";
        return Database::query($sql);
    }

    public static function find($memberId)
    {
        $sql = "SELECT * FROM MEMBER WHERE member_id = :memberId";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function login($email, $password)
    {
        $sql = "SELECT * FROM MEMBER WHERE email = :email";
        $params = [':email' => $email];
        $user = Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);

        if (is_array($user) && password_verify($password, $user['password'])) {
            error_log(print_r($user, true));
            return $user;
        }
        return false;
    }

    public static function getUserByEmail($email)
    {
        $sql = "SELECT * FROM MEMBER WHERE email = :email";
        $params = [':email' => $email];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateUser($userId, $data)
    {
        $sql = "UPDATE MEMBER 
                SET first_name = :first_name, 
                    last_name = :last_name, 
                    email = :email, 
                    birth_date = :birth_date, 
                    address = :address, 
                    phone = :phone, 
                    status = :status
                WHERE member_id = :member_id";

        $params = [
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':email' => $data['email'],
            ':birth_date' => $data['birth_date'],
            ':address' => $data['address'],
            ':phone' => $data['phone'],
            ':status' => $data['status'],
            ':member_id' => $userId
        ];

        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function deleteUser($memberId)
    {
        $sql = "DELETE FROM MEMBER WHERE member_id = :memberId";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function getSubscription($memberId)
    {
        $sql = "SELECT * FROM SUBSCRIPTION WHERE member_id = :memberId AND status = 'Active'";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function addSubscription($memberId, $subscriptionType, $startDate, $endDate, $amount)
    {
        $sql = "INSERT INTO SUBSCRIPTION (member_id, subscription_type, start_date, end_date, amount) 
                VALUES (:memberId, :subscriptionType, :startDate, :endDate, :amount)";
        $params = [
            ':memberId' => $memberId,
            ':subscriptionType' => $subscriptionType,
            ':startDate' => $startDate,
            ':endDate' => $endDate,
            ':amount' => $amount
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function searchUsers($searchTerm)
    {
        $sql = "SELECT first_name, last_name, email, status FROM MEMBER
                WHERE first_name LIKE :searchTerm1
                OR last_name LIKE :searchTerm2
                OR email LIKE :searchTerm3";
        $params = [
            ':searchTerm1' => '%' . $searchTerm . '%',
            ':searchTerm2' => '%' . $searchTerm . '%',
            ':searchTerm3' => '%' . $searchTerm . '%'
        ];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByEmail($email)
    {
        $sql = "SELECT * FROM MEMBER WHERE email = :email";
        $params = [':email' => $email];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($userData)
    {
        $verificationToken = bin2hex(random_bytes(32));
        $password = $userData['password'] ?? 'GOOGLE_USER';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO MEMBER (email, password, first_name, last_name, birth_date, address, phone, verification_token, is_verified)
                VALUES (:email, :password, :first_name, :last_name, :birth_date, :address, :phone, :verification_token, FALSE)";
        $params = [
            ':email' => $userData['email'],
            ':password' => $hashedPassword,
            ':first_name' => $userData['first_name'],
            ':last_name' => $userData['last_name'],
            ':birth_date' => $userData['birth_date'],
            ':address' => $userData['address'],
            ':phone' => $userData['phone'],
            ':verification_token' => $verificationToken
        ];

        $result = Database::query($sql, $params);

        error_log(print_r($params, true));
        error_log(print_r($userData, true));

        if ($result->rowCount() > 0) {
            self::sendVerificationEmail($userData['email'], $verificationToken);
            return Database::getConnection()->lastInsertId();
        }
        return false;
    }

    private static function sendVerificationEmail($to, $token)
    {
        $mail_parts = Config::get("mail_parts");

        $verify_url = Config::get("server_url") . "/verify-mail?token=" . $token;
        $title = "Vérifiez votre adresse mail - " . Config::get("brand", "Sportify");

        $paragraph = "<span>
          Vous êtes sur le point de rejoindre l'aventure Sportify et de booster
          vos performances grâce à notre expérience connectée unique.
        </span>
        <p>
          Pour valider votre inscription et profiter pleinement de l'expérience
          Sportify, veuillez confirmer votre adresse email en cliquant sur le
          bouton ci-dessous :
          <a href='[VERIFY_URL]'>[ANCHOR]</a>
        </p>
        <span>En validant votre email, vous débloquerez l'accès à :</span>
        <ul>
          <li>
            Le suivi personnalisé de vos entraînements pour mesurer vos progrès
            et atteindre vos objectifs.
          </li>
          <li>
            Des programmes conçus pour vous par nos coachs experts, adaptés à
            votre niveau et vos envies.
          </li>
          <li>
            La connexion à notre communauté motivante pour partager vos succès
            et vous dépasser ensemble.
          </li>
          <li>
            Et bien plus encore ! (Accès à des challenges, des conseils
            bien-être, etc.)
          </li>
        </ul>
        <p>
          Si le bouton ci-dessus ne fonctionne pas, vous pouvez également copier
          et coller le lien suivant dans votre navigateur : [VERIFY_URL] Ce lien de validation est unique.
        </p>";

        $mail_parts['mail_body'] = str_replace("[TITLE]", $title, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[PARAGRAPH]", $paragraph, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[VERIFY_URL]", $verify_url, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[ANCHOR]", "Vérifier mon mail", $mail_parts['mail_body']);

        $subject = $title;

        $message = $mail_parts['mail_head'] .
            $mail_parts['mail_title'] .
            $mail_parts['mail_head_end'] .
            $mail_parts['mail_body'] .
            $mail_parts['mail_footer'];

        $headers = "From: sportify@alwaysdata.net\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($to, $subject, $message, $headers)) {
            return true;
        } else {
            error_log("Erreur d'envoi d'email à $to: " . error_get_last());
            return false;
        }
    }

    public static function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE MEMBER SET password = :password WHERE member_id = :userId";
        $params = [
            ':password' => $hashedPassword,
            ':userId' => $userId
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function updateUserProfile($userId, $data)
    {
        $currentUser = self::getUserById($userId);

        if (!$currentUser) {
            throw new \Exception("Utilisateur non trouvé.");
        }

        $allowedFields = ['first_name', 'last_name', 'birth_date', 'address', 'phone', 'status', 'profile_picture'];
        $setFields = [];
        $params = [':userId' => $userId];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields) && $key !== 'email' && $value !== $currentUser[$key]) {
                $setFields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (!empty($data['password'])) {
            $setFields[] = "password = :password";
            $params[':password'] = $data['password'];
        }

        if (empty($setFields)) {
            return false;
        }

        $setClause = implode(', ', $setFields);
        $sql = "UPDATE MEMBER SET $setClause WHERE member_id = :userId";
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function getUserById($memberId)
    {
        $sql = "SELECT * FROM MEMBER WHERE member_id = :memberId";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function verifyCurrentPassword($userId, $currentPassword)
    {
        $sql = "SELECT password FROM MEMBER WHERE member_id = :userId";
        $params = [':userId' => $userId];
        $user = Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return $user && password_verify($currentPassword, $user['password']);
    }

    public static function verifyEmail($token)
    {
        $sql = "UPDATE MEMBER SET is_verified = TRUE, verification_token = NULL WHERE verification_token = :token";
        $params = [':token' => $token];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function getGoogleUserByEmail($email)
    {
        $sql = "SELECT * FROM MEMBRE WHERE email = :email";
        $params = [':email' => $email];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function registerGoogleUser($userData)
    {
        $sql = "INSERT INTO MEMBRE (email, first_name, last_name, google_id) VALUES (:email, :first_name, :last_name, :google_id)";
        $params = [
            ':email' => $userData['email'],
            ':first_name' => $userData['first_name'],
            ':last_name' => $userData['last_name'],
            ':google_id' => $userData['google_id']
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function storeResetToken($email, $token)
    {
        $sql = "UPDATE MEMBER SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email";
        $params = [':token' => $token, ':email' => $email];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function resetPassword($token, $newPassword)
    {
        $user = self::findByResetToken($token);

        if ($user) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $sql = "UPDATE MEMBER SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE member_id = :userId";
            $params = [':password' => $hashedPassword, ':userId' => $user['member_id']];
            return Database::query($sql, $params)->rowCount() > 0;
        }

        return false;
    }

    public static function findByResetToken($token)
    {
        $sql = "SELECT * FROM MEMBER WHERE reset_token = :token AND reset_token_expiry > NOW()";
        $params = [':token' => $token];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function sendPasswordResetEmail($email, $token)
    {
        $mail_parts = Config::get("mail_parts");

        $verify_url = Config::get("server_url") . "/reset-password?token=" . $token;
        $title = "Réinitialisation de votre mot de passe " . Config::get("brand", "Sportify");

        $mail_parts['mail_body'] = str_replace("[TITLE]", $title, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[PARAGRAPH]", "Merci de cliquer sur ce lien pour réinitialiser votre mot de passe : ", $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[VERIFY_URL]", $verify_url, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[ANCHOR]", "Changer mon mot de passe", $mail_parts['mail_body']);

        $subject = $title;

        $message = $mail_parts['mail_head'] .
            $mail_parts['mail_title'] .
            $mail_parts['mail_head_end'] .
            $mail_parts['mail_body'] .
            $mail_parts['mail_footer'];

        $headers = "From: sportify@alwaysdata.net\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($email, $subject, $message, $headers)) {
            return true;
        } else {
            error_log("Erreur d'envoi d'email de réinitialisation à $email: " . error_get_last());
            return false;
        }
    }

    
    public static function searchMembers($term)
    {
         $sql = "SELECT member_id, first_name, last_name, email, status FROM MEMBER
               WHERE first_name LIKE :searchTerm1
               OR last_name LIKE :searchTerm2
               OR email LIKE :searchTerm3";
         $params = [
             ':searchTerm1' => '%' . $term . '%',
             ':searchTerm2' => '%' . $term . '%',
             ':searchTerm3' => '%' . $term . '%'
         ];
         $results = Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
         foreach ($results as &$row) {
         }
   
        return $results;
    }

}