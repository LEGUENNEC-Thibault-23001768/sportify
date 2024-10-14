<?php


namespace Tests\Core;


use PHPUnit\Framework\TestCase;
use Core\Router;


class RouterTest extends TestCase {
    private $router;

    protected function setUp(): void {
        $this->router = new Router();
    }

    public function testAddRoute() {
        $this->router->addRoute("GET","/test","TestController", "testAction");

        $routes = $this->router->getRoutes();

        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('/test',$routes['GET']);

        $this->assertEquals(['controller' => 'TestController', 'action' => 'testAction'], $routes['GET']['/test']);
    }

    public function testDispatchRouteExists() {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->router->addRoute('GET', '/test', 'Tests\Core\MockController', 'testAction');

        $result = $this->router->dispatch('/test');

        $this->assertEquals(MockController::testAction(), $result);
    }

    public function testDispatchRouteNotExists() {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No routes defined for method: GET');
        
        $this->router->dispatch('/invalid');
    }

    public function testDispatchControllerNotExists() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->router->addRoute('GET', '/test','InvalidController','testAction');

        $this->expectException(\Exception::class);

        $this->expectExceptionMessage("Controller not found: InvalidController");

        $this->router->dispatch("/test");
    }

    public function testDispatchControllerActionNotExists() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->router->addRoute('GET', '/test','Tests\Core\MockController','invalidAction');

        $this->expectException(\Exception::class);

        $this->expectExceptionMessage("Action: invalidAction not found in controller: Tests\Core\MockController");
        
        $this->router->dispatch("/test");
    }

}

class MockController {
    public static function testAction() {
        return 'The controller action is correctly called.';
    }
}