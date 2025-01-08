<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Config;

class ConfigTest extends TestCase
{
    private $testConfigPath;

    protected function setUp(): void
    {
        $this->testConfigPath = __DIR__ . '/test_config.php';
        file_put_contents($this->testConfigPath, "<?php return ['test_key' => 'test_value', 'nested' => ['key' => 'nested_value']];");
         Config::load($this->testConfigPath);
    }

    protected function tearDown(): void
    {
      if(file_exists($this->testConfigPath)){
           unlink($this->testConfigPath);
         }
    }

    public function testLoadConfig()
     {
         $this->assertFileExists($this->testConfigPath);
        $this->assertNotNull(Config::get('test_key'));
       $this->assertNotNull(Config::get('nested'));

     }
    

    public function testGetConfigValue()
    {
        $this->assertEquals('test_value', Config::get('test_key'));
        $this->assertEquals(['key' => 'nested_value'], Config::get('nested'));
        $this->assertEquals('nested_value', Config::get('nested')['key']);

    }

    public function testGetConfigValueWithDefault()
    {
      
       $this->assertEquals('default_value', Config::get('nonexistent_key', 'default_value'));
       $this->assertNull(Config::get('nonexistent_key'));
    }

}