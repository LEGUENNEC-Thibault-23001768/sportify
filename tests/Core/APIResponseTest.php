<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\APIResponse;

class APIResponseTest extends TestCase
{
    public function testSetData()
    {
        $response = new APIResponse();
        $data = ['message' => 'hello'];
        $response->setData($data);
        $this->assertEquals($data, $response->getData());
    }

     public function testSetStatusCode()
    {
        $response = new APIResponse();
        $statusCode = 404;
        $response->setStatusCode($statusCode);
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testAddHeader()
    {
        $response = new APIResponse();
        $response->addHeader('Content-Type', 'application/xml');
        $this->assertEquals('application/xml', $response->getHeaders()['Content-Type']);
    }

    public function testSendJsonResponse()
     {
         $response = new APIResponse(['message' => 'Test message'], 201, ['X-Test-Header' => 'test-value']);

         ob_start();
         $response->send();
         $output = ob_get_clean();
    
         $this->assertEquals(201, http_response_code());
         $this->assertStringContainsString('X-Test-Header: test-value', xdebug_get_headers()[0]);
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'Test message']), $output);
    
      }

    public function testSendNoContent()
    {
        $response = new APIResponse(null, 204);

        ob_start();
        $response->send();
         $output = ob_get_clean();

        $this->assertEquals(204, http_response_code());
        $this->assertEmpty($output);
    }

    public function testSendDefaultStatusCode()
    {
        $response = new APIResponse(['message' => 'Default status code']);
        ob_start();
        $response->send();
        ob_get_clean();
    
        $this->assertEquals(200, http_response_code());
    }
    
     public function testSendDefaultContentType()
      {
       $response = new APIResponse(['message' => 'Default Content-Type'], 200, ['Content-Type' => 'application/json']);

        ob_start();
        $response->send();
         ob_get_clean();
    
         $this->assertStringContainsString('Content-Type: application/json', xdebug_get_headers()[0]);
       }


    public function testSendNoData()
    {
         $response = new APIResponse(null, 404);

       ob_start();
       $response->send();
         $output = ob_get_clean();
      
         $this->assertEquals(404, http_response_code());
         $this->assertEmpty($output);
     }

     private function getData() {
            return $this->data;
    }
     private function getStatusCode() {
            return $this->statusCode;
        }

    private function getHeaders() {
            return $this->headers;
        }
}