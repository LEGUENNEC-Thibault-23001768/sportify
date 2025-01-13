<?php

use PHPUnit\Framework\TestCase;

class AutoloaderTest extends TestCase
{
    public function setUp(): void
    {
        // Ensure the autoloader is registered
        require_once __DIR__ . '/../Autoloader.php';
    }

    public function testAutoloaderLoadsExistingClasses()
    {
        $this->assertTrue(class_exists('Controllers\HomeController'));
        $this->assertTrue(class_exists('Models\User'));
        $this->assertTrue(class_exists('Core\Router'));
    }

    public function testAutoloaderHandlesNonexistentClasses()
    {
        $this->assertFalse(class_exists('NonexistentNamespace\NonexistentClass'));
    }

    public function testAutoloaderRespectsNamespaces()
    {
        $homeController = new Controllers\HomeController();
        $this->assertInstanceOf('Controllers\HomeController', $homeController);
    }

    public function testAutoloaderRespectsNamespacesForModels()
    {
          $user = new Models\User();
         $this->assertInstanceOf('Models\User', $user);
    }
}