<?php

namespace Core;

class APIResponse
{
    private $data;
    private $statusCode;
    private $headers;

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
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        if ($this->data !== null) {
            header('Content-Type: application/json');
            echo json_encode($this->data);
        }

        exit;
    }
}