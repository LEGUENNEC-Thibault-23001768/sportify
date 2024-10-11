<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Database;
use PDO;

class DatabaseTest extends TestCase
{
    public function testGetInstance()
    {
        $instance1 = Database::getInstance();
        $instance2 = Database::getInstance();
        
        $this->assertInstanceOf(Database::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    public function testGetConnection()
    {
        $database = Database::getInstance();
        $connection = $database->getConnection();
        
        $this->assertInstanceOf(PDO::class, $connection);
    }

    public function testConnectionIsWorking()
    {
        $database = Database::getInstance();
        $connection = $database->getConnection();
        
        $stmt = $connection->query('SELECT 1');
        $result = $stmt->fetch(PDO::FETCH_COLUMN);
        
        $this->assertEquals(1, $result);
    }
}