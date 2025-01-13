<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Router;

class RouterTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
         $this->router->addRoute('GET', '/', 'Controllers\HomeController@index');
    }


    public function testAddRoute()
    {
          
        $this->assertIsArray($this->router->getRoutes('GET'));
          $this->assertNotEmpty($this->router->getRoutes('GET'));
          $this->assertIsArray($this->router->getRoutes());
           $this->assertNotEmpty($this->router->getRoutes());
    }


    public function testDispatchRouteExists()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
       $this->router->addRoute('GET', '/test', 'Tests\Core\MockController@testAction');
       $route = $this->router->dispatch('/test', 'GET');

        $this->assertEquals('The controller action is correctly called.', $route);
    }

     public function testDispatchRouteNotExists() {
         $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No routes defined for method: GET');
         $this->router->dispatch('/invalid','GET');
    }

    public function testDispatchControllerNotExists()
    {
       $this->router->addRoute('GET', '/users', 'Controllers\NonExistentController@index');

       $this->expectException(\Exception::class);
         $this->expectExceptionMessage('Controller class not found: Controllers\NonExistentController');
         $this->router->dispatch('/users','GET');
    }

      public function testDispatchControllerActionNotExists()
    {
      $this->router->addRoute('GET', '/user-action', 'Tests\Core\MockController@invalidAction');
     $this->expectException(\Exception::class);
       $this->expectExceptionMessage("Controller action not found: Tests\Core\MockController@invalidAction");
       $this->router->dispatch('/user-action','GET');
    }
}
class MockController {
    public static function testAction() {
        return 'The controller action is correctly called.';
    }
}