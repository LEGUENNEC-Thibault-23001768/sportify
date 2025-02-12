<?php

namespace Tests\Core;

use Core\Database;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Core\Config;

class DatabaseTest extends TestCase
{
    private $configFilePath;

     protected function setUp(): void
    {
        $this->configFilePath = __DIR__ . '/../config_test.php';
         file_put_contents($this->configFilePath, '<?php return ["db_host" => "localhost", "db_name" => "testdb", "db_user" => "testuser", "db_pass" => "testpass"];');
        Config::load($this->configFilePath);
    }

     protected function tearDown(): void
    {
        if (file_exists($this->configFilePath)) {
            unlink($this->configFilePath);
        }
    }

    public function testGetConnection()
    {
         $conn = Database::getConnection();
        $this->assertInstanceOf(PDO::class, $conn);
    }

    public function testConnectionIsWorking()
    {
        $sql = "SELECT 1";
        try {
            $stmt = Database::query($sql);
            $result = $stmt->fetchColumn();
            $this->assertEquals(1, $result);
         } catch (PDOException $e) {
           $this->fail("Database query failed: " . $e->getMessage());
        }
    }
    
    public function testQueryThrowsExceptionOnInvalidSQL()
    {
         $this->expectException(PDOException::class);
        Database::query("INVALID SQL");
    }
}