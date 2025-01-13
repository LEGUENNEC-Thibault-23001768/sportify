<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Router;

class RouterTest extends TestCase
{
    private $router;

    public function setUp(): void
    {
        $this->router = new Router();
        $this->resetRoutes();
        $this->mockRouteProviders();
    }
   
   private function resetRoutes()
    {
        $reflection = new \ReflectionClass($this->router);
        $routes = $reflection->getProperty('routes');
        $routes->setAccessible(true);
        $routes->setValue($this->router, []);

        $initialized = $reflection->getProperty('initialized');
        $initialized->setAccessible(true);
        $initialized->setValue($this->router, false);

        $controllers = $reflection->getProperty('controllers');
        $controllers->setAccessible(true);
        $controllers->setValue($this->router, []);
    }
    
    private function mockRouteProviders(){
        $mockControllerClass = 'Tests\\MockController';
        eval('namespace Tests;
            use Core\RouteProvider;
            class MockController implements RouteProvider
            {
                public static function routes(): void
                {
                   \Core\Router::get("/mock-route", "' . $mockControllerClass . '@index");
                   \Core\Router::apiResource("/mock-api-resource", "' . $mockControllerClass . '");
                }
                public function index()
                {
                     return "Mock Controller Index";
                }
                public function get($id = null) {
                     if($id){
                        return json_encode(["statusCode"=> 200, "data" => ["message"=> "Mock API Controller Get with ID " . $id]]);
                     }
                     return json_encode(["statusCode"=> 200, "data" => ["message"=> "Mock API Controller Get"]]);
                 }
                 public function post() {
                     return json_encode(["statusCode"=> 200, "data" => ["message"=> "Mock API Controller Post"]]);
                 }
                 public function put($id = null) {
                      if($id){
                        return json_encode(["statusCode"=> 200, "data" => ["message"=> "Mock API Controller Put with ID " . $id]]);
                      }
                     return json_encode(["statusCode"=> 200, "data" => ["message"=> "Mock API Controller Put"]]);
                 }
                 public function delete($id = null) {
                      return json_encode(["statusCode"=> 200, "data" => ["message"=> "Mock API Controller Delete with ID " . $id]]);
                 }
            }');

          $mockControllerClass = 'Tests\\MockApiController';
        eval('namespace Tests;
            use Core\RouteProvider;
            use Core\APIController;

            class MockApiController extends APIController implements RouteProvider
            {
                 public static function routes(): void
                {
                  \Core\Router::get("/mock-api-route", "' . $mockControllerClass . '@get");
                 }
                 public function get() {
                     return json_encode(["statusCode"=> 200, "data" => ["message"=> "Mock API Controller Get"]]);
                 }
            }');
          
        $mockControllerClass = 'Tests\\MockApiControllerMethod';
        eval('namespace Tests;
            use Core\RouteProvider;
            use Core\APIController;

           class MockApiControllerMethod extends APIController implements RouteProvider
            {
                 public static function routes(): void
                {
                  \Core\Router::get("/mock-api-method", "' . $mockControllerClass . '");
                 }
                 public function get() {
                     return json_encode(["statusCode"=> 200, "data" => ["message"=> "Mock API Controller Get"]]);
                 }
            }');
    }

    private function getRoutes() {
        $reflection = new \ReflectionClass($this->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        return $property->getValue($this->router);
    }

    public function testAddRoute()
    {
        $this->router->get('/test', function() { return 'test'; });
        $routes =  $this->getRoutes();
        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('/test', $routes['GET']);
        $this->assertIsCallable($routes['GET']['/test']['handler']);
        $this->assertNull($routes['GET']['/test']['middleware']);
    }

    public function testAddRouteWithMiddleware()
    {
        $middleware = function () { return true; };
        $this->router->get('/test', function() { return 'test'; }, $middleware);
         $routes =  $this->getRoutes();
         $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('/test', $routes['GET']);
        $this->assertIsCallable($routes['GET']['/test']['handler']);
        $this->assertIsCallable($routes['GET']['/test']['middleware']);
    }

    public function testExactRouteMatch()
    {
        $this->router->get('/test', function () { return 'test'; });
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectOutputString('test');
        $this->router->dispatch('/test');
    }
    
     public function testParameterizedRouteMatch()
    {
        $this->router->get('/users/{id}', function ($id) {
            return "User ID: " . $id;
        });
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectOutputString("User ID: 123");
       $this->router->dispatch('/users/123');
    }

      public function testDispatchToControllerAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
         $this->expectOutputString("Mock Controller Index");
         $this->router->dispatch('/mock-route');
    }

    public function testDispatchToApiControllerAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $response =  $this->router->dispatch('/mock-api-route');
        $this->assertEquals(200, json_decode($response, true)['statusCode']);
       $this->assertStringContainsString("Mock API Controller Get", json_decode($response, true)['data']['message']);
    }
   
    public function testRouteNotFound()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectException(\Exception::class);
       $this->expectExceptionMessage("No route found for URL: /non-existent with method: GET");
        $this->router->dispatch('/non-existent');
    }
    
     public function testApiResource()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $response =  $this->router->dispatch('/mock-api-resource');
        $this->assertEquals(200, json_decode($response, true)['statusCode']);
        $this->assertStringContainsString("Mock API Controller Get", json_decode($response, true)['data']['message']);
    }
}