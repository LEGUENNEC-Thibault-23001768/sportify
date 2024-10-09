<?php
// src/Models/User.php

namespace Models;

use Core\Database;
use PDO;

class User
{
    private static function getDb()
    {
        return Database::getInstance()->getConnection();
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
        $query = "INSERT INTO MEMBER (email, password, first_name, last_name, birth_date, address, phone, verification_token, is_verified)
                  VALUES (:email, :password, :first_name, :last_name, :birth_date, :address, :phone, :verification_token, FALSE)";

        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            'email' => $userData['email'],
            'password' => $userData['password'],
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'birth_date' => $userData['birth_date'],
            'address' => $userData['address'],
            'phone' => $userData['phone'],
            'verification_token' => $verificationToken
        ]);

        if ($result) {
            self::sendVerificationEmail($userData['email'], $verificationToken);
            return $db->lastInsertId();
        }
        return false;
    }

    private static function sendVerificationEmail($to, $token)
    {
        $subject = "Vérifiez votre adresse email";
        $verifyUrl = "http://localhost:8080/verify-email?token=" . $token;
        $message = "Cliquez sur ce lien pour vérifier votre email : $verifyUrl";
        $headers = "From: sportify@alwaysdata.net\r\n";
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

}
