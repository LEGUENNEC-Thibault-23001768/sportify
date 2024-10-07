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
    private $conn;

    private function __construct()
    {
        $charset = 'utf8mb4';

        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . $charset;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
