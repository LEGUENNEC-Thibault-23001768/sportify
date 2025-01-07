<?php

namespace Core;

use Core\APIResponse;

abstract class APIController
{
    protected function get($id = null)
    {
        $response = new APIResponse();
        return $response->setStatusCode(405)->setData(['error' => 'GET method not allowed.'])->send();
    }

    protected function post()
    {
        $response = new APIResponse();
        return $response->setStatusCode(405)->setData(['error' => 'POST method not allowed.'])->send();
    }

    protected function put($id = null)
    {
        $response = new APIResponse();
        return $response->setStatusCode(405)->setData(['error' => 'PUT method not allowed.'])->send();
    }

    protected function delete($id = null)
    {
        $response = new APIResponse();
        return $response->setStatusCode(405)->setData(['error' => 'DELETE method not allowed.'])->send();
    }

    public function handleRequest($method, ...$params)
    {
         if (method_exists($this, strtolower($method))) {
           return call_user_func_array([$this, strtolower($method)], $params);
        }
        $response = new APIResponse();
        return $response->setStatusCode(405)->setData(['error' => 'Method not allowed.'])->send();
    }
}