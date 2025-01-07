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

    public function handleRequest($method, $id = null)
    {
        switch ($method) {
            case 'GET':
                return $this->get($id);
            case 'POST':
                return $this->post();
            case 'PUT':
                return $this->put($id);
            case 'DELETE':
                return $this->delete($id);
            default:
                $response = new APIResponse();
                return $response->setStatusCode(405)->setData(['error' => 'Method not allowed.'])->send();
        }
    }
}