<?php

namespace Core;

class APIResponse
{
    private mixed $data;
    private int $statusCode;
    private array $headers;

    /**
     * @param $data
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct($data = null, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode): static
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addHeader($key, $value): static
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * @return void
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        if ($this->data !== null) {
            header('Content-Type: application/json');
            echo json_encode($this->data);
        } else return;
    }
}