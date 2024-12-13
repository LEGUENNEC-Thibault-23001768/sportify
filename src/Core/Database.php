<?php

namespace Core;

use PDO;
use PDOException;

// Configuration de la base de données
define('DB_HOST', 'mysql-sportify.alwaysdata.net');
define('DB_USER', 'sportify');
define('DB_PASS', 'lechatrouge');
define('DB_NAME', 'sportify_db');

class Database
{
    private static $instance = null;
    private static $conn;

    private function __construct()
    {
        // Private constructor to prevent instantiation
    }

    private static function connect()
    {
        if (self::$conn === null) {
            $charset = 'utf8mb4';
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . $charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$conn = new PDO($dsn, DB_USER, DB_PASS, $options);
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