<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\APIResponse;

class APIResponseTest extends TestCase
{
    public function testSetStatusCode()
    {
        $response = new APIResponse();
        $response->setStatusCode(201);
        $this->assertEquals(201, $this->getStatusCode($response));
    }

    public function testSetData()
    {
         $response = new APIResponse();
        $data = ['message' => 'Test Data'];
        $response->setData($data);
        $this->assertEquals($data, $this->getData($response));
    }

    public function testAddHeader()
    {
         $response = new APIResponse();
        $response->addHeader('Content-Type', 'application/json');
        $this->assertArrayHasKey('Content-Type', $this->getHeaders($response));
        $this->assertEquals('application/json', $this->getHeaders($response)['Content-Type']);
    }
    
     public function testSendResponseWithData()
    {
        $response = new APIResponse(['test' => 'data'], 201, ['Content-Type' => 'application/json']);
         ob_start();
         $response->send();
        $output = ob_get_clean();

        $this->assertEquals(201, http_response_code());
          $headers = headers_list();
        $this->assertStringContainsString("Content-Type: application/json", $headers[0]);
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString('{"test":"data"}', $output);
    }

    public function testDefaultConstructorValues()
    {
        $response = new APIResponse();
        $this->assertNull($this->getData($response));
        $this->assertEquals(200, $this->getStatusCode($response));
        $this->assertEmpty($this->getHeaders($response));
    }

    private function getStatusCode(APIResponse $response) {
         $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        return $property->getValue($response);
    }
    
    private function getData(APIResponse $response) {
         $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('data');
         $property->setAccessible(true);
        return $property->getValue($response);
    }
    
    private function getHeaders(APIResponse $response) {
         $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('headers');
         $property->setAccessible(true);
        return $property->getValue($response);
    }
}