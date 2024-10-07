<?php 


namespace Core;

class Router
{
    private $routes = [];

    public function addRoute($method, $url, $controller, $action)
    {
        $this->routes[$method][$url] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($url)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (isset($this->routes[$method][$url])) {
            $controller = $this->routes[$method][$url]['controller'];
            $action = $this->routes[$method][$url]['action'];
            
            if (!class_exists($controller)) {
                throw new \Exception("Controller not found: $controller");
            }
            
            try {
                $controllerInstance = new $controller();
            } catch (\Throwable $e) {
                throw new \Exception("Error creating controller instance: " . $e->getMessage());
            }
            
            if (!method_exists($controllerInstance, $action)) {
                throw new \Exception("Action not found in controller: $action");
            }

            $controllerInstance->$action();
        } else {
            throw new \Exception("No route found for URL: $url with method: $method");
        }
    }
}
