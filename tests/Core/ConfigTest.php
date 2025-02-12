<?php
namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Config;

class ConfigTest extends TestCase
{
    private $configFilePath;

    protected function setUp(): void
    {
        $this->configFilePath = __DIR__ . '/../config_test.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->configFilePath)) {
            unlink($this->configFilePath);
        }
    }

    public function testLoadConfig()
    {
        file_put_contents($this->configFilePath, '<?php return ["test_key" => "test_value"];');
        Config::load($this->configFilePath);
        $this->assertEquals("test_value", Config::get("test_key"));
    }

    public function testGetExistingConfig()
    {
        file_put_contents($this->configFilePath, '<?php return ["setting1" => "value1"];');
        Config::load($this->configFilePath);
        $this->assertEquals('value1', Config::get('setting1'));
    }

    public function testGetNonExistingConfigWithDefault()
    {
        file_put_contents($this->configFilePath, '<?php return [];');
        Config::load($this->configFilePath);
        $this->assertEquals('default', Config::get('non_existing_key', 'default'));
    }
    
    public function testGetNonExistingConfigWithoutDefault()
    {
        file_put_contents($this->configFilePath, '<?php return [];');
        Config::load($this->configFilePath);
       $this->assertNull(Config::get('non_existing_key'));
    }
}