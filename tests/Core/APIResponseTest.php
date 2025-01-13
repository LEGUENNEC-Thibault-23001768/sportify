<?php

namespace Core;

class APIResponse
{
    private $data;
    private $statusCode;
    private $headers = ['Content-Type' => 'application/json'];

    public function __construct($data = null, $statusCode = 200, $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = array_merge($this->headers, $headers);

    }


    public function setData($data)
    {
        $this->data = $data;
    }

       public function getData()
    {
        return $this->data;
    }


    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

     public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function addHeader($header, $value)
    {
       $this->headers[$header] = $value;
    }
    
     public function getHeaders()
    {
        return $this->headers;
    }

    public function send()
    {
         http_response_code($this->statusCode);
        
         foreach($this->headers as $key => $header) {
               header($key.': '.$header);
            }
       

         if($this->data !== null) {
          echo json_encode($this->data);
         }
    }
}