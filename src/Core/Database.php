<?php

namespace Core;

use PDO;
use PDOException;
use Core\Config;


class Database
{
    private static $instance = null;
    private static $conn;

    private function __construct()
    {
    }

    private static function connect()
    {
        if (self::$conn === null) {
            $charset = 'utf8mb4';
            $dsn = "mysql:host=" . Config::get("db_host") . ";dbname=" . Config::get("db_name") . ";charset=" . $charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$conn = new PDO($dsn, Config::get("db_user"), Config::get("db_pass"), $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
    }

    public static function getConnection()
    {
        self::connect();
        return self::$conn;
    }

    public static function query($sql, $params = [])
    {
        self::connect();
        try {
            $stmt = self::$conn->prepare($sql);
            $stmt->execute($params);

            return $stmt;
        } catch (PDOException $e) {
            throw new PDOException("Query error: " . $e->getMessage(), (int)$e->getCode());
        }
    }
}