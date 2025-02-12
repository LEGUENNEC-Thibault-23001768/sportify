<?php
namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\APIController;

class APIControllerTest extends TestCase
{
    public function testDefaultMethodsReturn405()
    {
        $controller = new class extends APIController {};
        
        $responseGet = $controller->handleRequest('GET');
        $responsePost = $controller->handleRequest('POST');
        $responsePut = $controller->handleRequest('PUT');
        $responseDelete = $controller->handleRequest('DELETE');

        $this->assertEquals(405, json_decode($responseGet, true)['statusCode']);
        $this->assertEquals(405, json_decode($responsePost, true)['statusCode']);
        $this->assertEquals(405, json_decode($responsePut, true)['statusCode']);
        $this->assertEquals(405, json_decode($responseDelete, true)['statusCode']);

        $this->assertStringContainsString('GET method not allowed.', json_decode($responseGet, true)['data']['error']);
        $this->assertStringContainsString('POST method not allowed.', json_decode($responsePost, true)['data']['error']);
        $this->assertStringContainsString('PUT method not allowed.', json_decode($responsePut, true)['data']['error']);
        $this->assertStringContainsString('DELETE method not allowed.', json_decode($responseDelete, true)['data']['error']);
    }

     public function testHandleRequestCallsCorrectMethod()
    {
        $controller = new class extends APIController {
            public function get($id = null) {
               return json_encode(['statusCode'=>200, 'data'=>['message' => 'get method called']]);
            }
        };

        $responseGet = $controller->handleRequest('GET');
        $this->assertEquals(200, json_decode($responseGet, true)['statusCode']);
        $this->assertStringContainsString('get method called', json_decode($responseGet, true)['data']['message']);
    }
    
     public function testHandleRequestReturns405ForInvalidMethod()
    {
        $controller = new class extends APIController {};
        $response = $controller->handleRequest('PATCH');
        $this->assertEquals(405, json_decode($response, true)['statusCode']);
        $this->assertStringContainsString('Method not allowed.', json_decode($response, true)['data']['error']);
    }
}