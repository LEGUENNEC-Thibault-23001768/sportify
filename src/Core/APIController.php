<?php

namespace Core;

class APIController
{
    public function handleRequest($method, $id = null)
    {
        switch ($method) {
            case 'GET':
              if (method_exists($this, 'get')) {
                   return $this->get($id);
                }
                break;

             case 'POST':
                if (method_exists($this, 'post')) {
                   return $this->post();
                }
               break;


            case 'PUT':
              if (method_exists($this, 'put')) {
                    return $this->put($id);
                }
                break;
           case 'DELETE':
                 if (method_exists($this, 'delete')) {
                     return $this->delete($id);
                 }
               break;
           default:
                
        }
        return new APIResponse(['error' => 'Method not allowed.'],405);
    }
}