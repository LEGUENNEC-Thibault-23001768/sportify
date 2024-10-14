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

    public function create($userData)
{
    $db = self::getDb();
    
    $password = $userData['password'] ?? 'GOOGLE_USER';
    
    $query = "INSERT INTO MEMBER (email, password, first_name, last_name, birth_date, address, phone)
              VALUES (:email, :password, :first_name, :last_name, :birth_date, :address, :phone)";

    $stmt = $db->prepare($query);
    return $stmt->execute([
        'email' => $userData['email'],
        'password' => $password,  
        'first_name' => $userData['first_name'],
        'last_name' => $userData['last_name'],
        'birth_date' => $userData['birth_date'] ?? null,
        'address' => $userData['address'] ?? null,
        'phone' => $userData['phone'] ?? null
    ]);
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


    public static function updateUserProfile($userId, $data)
    {
        $db = self::getDb();

        $allowedFields = ['first_name', 'last_name', 'email', 'birth_date', 'address', 'phone'];
        $setFields = [];
        $params = ['userId' => $userId];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $setFields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        if (!empty($data['new_password'])) {
            $setFields[] = "password = :password";
            $params['password'] = password_hash($data['new_password'], PASSWORD_BCRYPT);
        }

        if (empty($setFields)) {
            return false;
        }

        $setClause = implode(', ', $setFields);
        $query = "UPDATE MEMBER SET $setClause WHERE member_id = :userId";
        $stmt = $db->prepare($query);

        return $stmt->execute($params);
    }


    public static function verifyCurrentPassword($userId, $currentPassword)
    {
        $db = self::getDb();
        $query = "SELECT password FROM MEMBER WHERE member_id = :userId";
        $stmt = $db->prepare($query);
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user && password_verify($currentPassword, $user['password']);
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


}
