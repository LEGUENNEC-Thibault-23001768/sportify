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
}
