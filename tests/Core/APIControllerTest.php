<?php
namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\APIController;
use Core\APIResponse;

class MockAPIController extends APIController {
    public function get($id = null) {
        return new APIResponse(['message' => 'GET method called', 'id' => $id]);
    }
    public function post() {
        return new APIResponse(['message' => 'POST method called']);
    }

    public function put($id = null) {
        return new APIResponse(['message' => 'PUT method called', 'id' => $id]);
    }

    public function delete($id = null) {
         return new APIResponse(['message' => 'DELETE method called', 'id' => $id]);
    }
}
class APIControllerTest extends TestCase
{
   
    public function testHandleGetMethod() // <-- "test" added here
    {
         $controller = new MockAPIController();
        $result = $controller->handleRequest('GET', 123);

         $this->assertEquals(200, http_response_code());
        $this->assertEquals('application/json', $result['headers']['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'GET method called', 'id' => 123]), $result['data']);

    }

    public function testHandlePostMethod() // <-- "test" added here
    {
        $controller = new MockAPIController();
       $result = $controller->handleRequest('POST');
         
      $this->assertEquals(200, http_response_code());
      $this->assertEquals('application/json', $result['headers']['Content-Type']);
      $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'POST method called']), $result['data']);
    }
    
     public function testHandlePutMethod() // <-- "test" added here
    {
          $controller = new MockAPIController();
         $result = $controller->handleRequest('PUT', 456);
    
        $this->assertEquals(200, http_response_code());
        $this->assertEquals('application/json', $result['headers']['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'PUT method called', 'id' => 456]), $result['data']);
    }

     public function testHandleDeleteMethod() // <-- "test" added here
     {
        $controller = new MockAPIController();
        $result = $controller->handleRequest('DELETE', 789);
        
        $this->assertEquals(200, http_response_code());
        $this->assertEquals('application/json', $result['headers']['Content-Type']);
         $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'DELETE method called', 'id' => 789]), $result['data']);
    }

    public function testHandleInvalidMethod() { // <-- "test" added here
        $controller = new MockAPIController();
        $result = $controller->handleRequest('PATCH', 123);
       
        $this->assertEquals(405, http_response_code());
        $this->assertEquals('application/json', $result['headers']['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Method not allowed.']), $result['data']);
    }

   public function testHandleGetMethodNotAllowed() // <-- "test" added here
    {
        $controller = new class extends APIController{};
        $result = $controller->handleRequest('GET', 123);

         $this->assertEquals(405, http_response_code());
        $this->assertEquals('application/json', $result['headers']['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'GET method not allowed.']), $result['data']);

    }

      public function testHandlePostMethodNotAllowed() // <-- "test" added here
    {
        $controller = new class extends APIController{};
       $result = $controller->handleRequest('POST');
         
      $this->assertEquals(405, http_response_code());
      $this->assertEquals('application/json', $result['headers']['Content-Type']);
      $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'POST method not allowed.']), $result['data']);
    }
    
     public function testHandlePutMethodNotAllowed() // <-- "test" added here
    {
          $controller = new class extends APIController{};
         $result = $controller->handleRequest('PUT', 456);
    
        $this->assertEquals(405, http_response_code());
        $this->assertEquals('application/json', $result['headers']['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'PUT method not allowed.']), $result['data']);
    }

     public function testHandleDeleteMethodNotAllowed() // <-- "test" added here
     {
        $controller = new class extends APIController{};
        $result = $controller->handleRequest('DELETE', 789);
        
        $this->assertEquals(405, http_response_code());
        $this->assertEquals('application/json', $result['headers']['Content-Type']);
         $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'DELETE method not allowed.']), $result['data']);
    }

}