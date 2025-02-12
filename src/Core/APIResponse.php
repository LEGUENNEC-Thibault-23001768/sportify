<?php

namespace Core;

class APIResponse
{
    private $data;
    private $statusCode;
    private $headers;
    private $sent = false;

    public function __construct($data = null, $statusCode = 200, $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send()
    {
      if ($this->sent) {
           return; // Prevent multiple sends
        }
      
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        if ($this->data !== null) {
            header('Content-Type: application/json');
            echo json_encode($this->data);
        }
        $this->sent = true;
    }
    
    public function getOutput() {
        if($this->data !== null){
          return json_encode($this->data);
        }
        return '';
    }
}