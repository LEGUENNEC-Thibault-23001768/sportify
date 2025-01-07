<?php

namespace Models;

use Core\Config;
use Core\Database;
use Exception;
use PDO;
use PDOStatement;
use Random\RandomException;

class User
{
    /**
     * @return array
     */
    public static function getAllUsers(): array
    {
        $sql = "SELECT member_id, first_name, last_name, email, status FROM MEMBER";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return false|PDOStatement
     */
    public static function getAll(): false|PDOStatement
    {
        $sql = "SELECT * FROM MEMBER";
        return Database::query($sql);
    }

    /**
     * @param $memberId
     * @return mixed
     */
    public static function find($memberId): mixed
    {
        $sql = "SELECT * FROM MEMBER WHERE member_id = :memberId";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $email
     * @param $password
     * @return array|false
     */
    public static function login($email, $password): false|array
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

    /**
     * @param $email
     * @return mixed
     */
    public static function getUserByEmail($email): mixed
    {
        $sql = "SELECT * FROM MEMBER WHERE email = :email";
        $params = [':email' => $email];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $userId
     * @param $data
     * @return bool
     */
    public static function updateUser($userId, $data): bool
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

    /**
     * @param $memberId
     * @return bool
     */
    public static function deleteUser($memberId): bool
    {
        $sql = "DELETE FROM MEMBER WHERE member_id = :memberId";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $memberId
     * @return mixed
     */
    public static function getSubscription($memberId): mixed
    {
        $sql = "SELECT * FROM SUBSCRIPTION WHERE member_id = :memberId AND status = 'Active'";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $memberId
     * @param $subscriptionType
     * @param $startDate
     * @param $endDate
     * @param $amount
     * @return bool
     */
    public static function addSubscription($memberId, $subscriptionType, $startDate, $endDate, $amount): bool
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

    /**
     * @param $searchTerm
     * @return array
     */
    public static function searchUsers($searchTerm): array
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

    /**
     * @param $email
     * @return mixed
     */
    public static function findByEmail($email): mixed
    {
        $sql = "SELECT * FROM MEMBER WHERE email = :email";
        $params = [':email' => $email];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $userData
     * @return false|string
     * @throws RandomException
     */
    public static function create($userData): false|string
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

    /**
     * @param $to
     * @param $token
     * @return bool
     */
    private static function sendVerificationEmail($to, $token): bool
    {
        $mail_parts = Config::get("mail_parts");

        $verify_url = Config::get("server_url" . "/verify-mail?token=" . $token);
        $title = "Vérifiez votre adresse mail - " . Config::get("brand", "Sportify");

        $mail_parts['mail_body'] = str_replace("[TITLE]", $title, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[PARAGRAPH]", "Merci de cliquer sur le lien ci-dessous pour vérifier votre adresse email :", $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[VERIFY_URL]", Config::get("server_url") . $verify_url, $mail_parts['mail_body']);
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

    /**
     * @param $userId
     * @param $newPassword
     * @return bool
     */
    public static function updatePassword($userId, $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE MEMBER SET password = :password WHERE member_id = :userId";
        $params = [
            ':password' => $hashedPassword,
            ':userId' => $userId
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $userId
     * @param $data
     * @return bool
     * @throws Exception
     */
    public static function updateUserProfile($userId, $data): bool
    {
        $currentUser = self::getUserById($userId);

        if (!$currentUser) {
            throw new Exception("Utilisateur non trouvé.");
        }

        $allowedFields = ['first_name', 'last_name', 'email', 'birth_date', 'address', 'phone', 'status', 'profile_picture'];
        $setFields = [];
        $params = [':userId' => $userId];

        if (!empty($data['email']) && $data['email'] !== $currentUser['email']) {
            $checkEmailSql = "SELECT * FROM MEMBER WHERE email = :email AND member_id != :userId";
            $checkEmailParams = [':email' => $data['email'], ':userId' => $userId];
            $existingUser = Database::query($checkEmailSql, $checkEmailParams)->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                error_log("L'adresse e-mail est déjà utilisée par un autre utilisateur.");
            } else {
                $setFields[] = "email = :email";
                $params[':email'] = $data['email'];
            }
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields) && $key !== 'email' && $value !== $currentUser[$key]) {
                $setFields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (!empty($data['new_password'])) {
            $setFields[] = "password = :password";
            $params[':password'] = password_hash($data['new_password'], PASSWORD_BCRYPT);
        }

        if (empty($setFields)) {
            return false;
        }

        $setClause = implode(', ', $setFields);
        $sql = "UPDATE MEMBER SET $setClause WHERE member_id = :userId";
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $memberId
     * @return mixed
     */
    public static function getUserById($memberId): mixed
    {
        $sql = "SELECT * FROM MEMBER WHERE member_id = :memberId";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $userId
     * @param $currentPassword
     * @return bool
     */
    public static function verifyCurrentPassword($userId, $currentPassword): bool
    {
        $sql = "SELECT password FROM MEMBER WHERE member_id = :userId";
        $params = [':userId' => $userId];
        $user = Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return $user && password_verify($currentPassword, $user['password']);
    }

    /**
     * @param $token
     * @return bool
     */
    public static function verifyEmail($token): bool
    {
        $sql = "UPDATE MEMBER SET is_verified = TRUE, verification_token = NULL WHERE verification_token = :token";
        $params = [':token' => $token];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $email
     * @return mixed
     */
    public static function getGoogleUserByEmail($email): mixed
    {
        $sql = "SELECT * FROM MEMBRE WHERE email = :email";
        $params = [':email' => $email];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $userData
     * @return bool
     */
    public static function registerGoogleUser($userData): bool
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

    /**
     * @param $email
     * @param $token
     * @return bool
     */
    public static function storeResetToken($email, $token): bool
    {
        $sql = "UPDATE MEMBER SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email";
        $params = [':token' => $token, ':email' => $email];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $token
     * @param $newPassword
     * @return bool
     */
    public static function resetPassword($token, $newPassword): bool
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

    /**
     * @param $token
     * @return mixed
     */
    public static function findByResetToken($token): mixed
    {
        $sql = "SELECT * FROM MEMBER WHERE reset_token = :token AND reset_token_expiry > NOW()";
        $params = [':token' => $token];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $email
     * @param $token
     * @return bool
     */
    public static function sendPasswordResetEmail($email, $token): bool
    {
        $mail_parts = Config::get("mail_parts");

        $verify_url = Config::get("server_url" . "/reset-password?token=" . $token);
        $title = "Réinitialisation de votre mot de passe " . Config::get("brand", "Sportify");

        $mail_parts['mail_body'] = str_replace("[TITLE]", $title, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[PARAGRAPH]", "Merci de cliquer sur ce lien pour réinitialiser votre mot de passe : ", $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[VERIFY_URL]", Config::get("server_url") . $verify_url, $mail_parts['mail_body']);
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

}