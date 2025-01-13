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
         return new APIResponse(['message' => 'POST method called'], 201);
     }

     public function put($id = null) {
         return new APIResponse(['message' => 'PUT method called', 'id' => $id], 202);
     }

     public function delete($id = null) {
         return new APIResponse(['message' => 'DELETE method called', 'id' => $id], 203);
     }
 }

 class APIControllerTest extends TestCase
 {
     public function testHandleGetMethod()
     {
         $controller = new MockAPIController();
         $result = $controller->handleRequest('GET', 123);

         $this->assertEquals(200, $result->getStatusCode());
         $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
         $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'GET method called', 'id' => 123]), json_encode($result->getData()));
     }

     public function testHandlePostMethod()
     {
         $controller = new MockAPIController();
        $result = $controller->handleRequest('POST');
         
       $this->assertEquals(201, $result->getStatusCode());
       $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
       $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'POST method called']), json_encode($result->getData()));
     }
     
      public function testHandlePutMethod()
     {
           $controller = new MockAPIController();
          $result = $controller->handleRequest('PUT', 456);
     
         $this->assertEquals(202, $result->getStatusCode());
         $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
         $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'PUT method called', 'id' => 456]), json_encode($result->getData()));
     }

      public function testHandleDeleteMethod()
      {
         $controller = new MockAPIController();
         $result = $controller->handleRequest('DELETE', 789);
         
        $this->assertEquals(203, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
          $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'DELETE method called', 'id' => 789]), json_encode($result->getData()));
     }

     public function testHandleInvalidMethod()
     {
         $controller = new MockAPIController();
         $result = $controller->handleRequest('PATCH', 123);
        
          $this->assertEquals(405, $result->getStatusCode());
         $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
          $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Method not allowed.']), json_encode($result->getData()));
     }

    public function testHandleGetMethodNotAllowed()
     {
         $controller = new class extends APIController{};
         $result = $controller->handleRequest('GET', 123);

          $this->assertEquals(405, $result->getStatusCode());
         $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
         $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Method not allowed.']), json_encode($result->getData()));
     }

     public function testHandlePostMethodNotAllowed()
     {
         $controller = new class extends APIController{};
        $result = $controller->handleRequest('POST');
         
       $this->assertEquals(405, $result->getStatusCode());
       $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
       $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Method not allowed.']), json_encode($result->getData()));
     }
     
      public function testHandlePutMethodNotAllowed()
     {
           $controller = new class extends APIController{};
          $result = $controller->handleRequest('PUT', 456);
     
         $this->assertEquals(405, $result->getStatusCode());
         $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
         $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Method not allowed.']), json_encode($result->getData()));
     }

      public function testHandleDeleteMethodNotAllowed()
      {
         $controller = new class extends APIController{};
         $result = $controller->handleRequest('DELETE', 789);
         
        $this->assertEquals(405, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaders()['Content-Type']);
         $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Method not allowed.']), json_encode($result->getData()));
     }
 }