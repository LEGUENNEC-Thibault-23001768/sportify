<?php

namespace Core;

use PDO;
use PDOException;
use PDOStatement;

// Configuration de la base de données
define('DB_HOST', 'mysql-sportify.alwaysdata.net');
define('DB_USER', 'sportify');
define('DB_PASS', 'lechatrouge');
define('DB_NAME', 'sportify_db');

class Database
{
    private static ?PDO $conn = null;

    private function __construct()
    {
        // Private constructor to prevent instantiation
    }

    /**
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        self::connect();
        return self::$conn;
    }

    /**
     * @return void
     */
    private static function connect(): void
    {
        if (self::$conn === null) {
            $charset = 'utf8mb4';
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . $charset;

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
    }

    /**
     * @param $sql
     * @param array $params
     * @return false|PDOStatement
     */
    public static function query($sql, array $params = []): false|PDOStatement
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