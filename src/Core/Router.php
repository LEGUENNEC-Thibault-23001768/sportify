<?php 


namespace Core;

class Router
{
    private $routes = [];

    public function addRoute($url, $controller, $action)
    {
        $this->routes[$url] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($url)
    {

        if (array_key_exists($url, $this->routes)) {
            $controller = $this->routes[$url]['controller'];
            $action = $this->routes[$url]['action'];
            
            $controllerInstance = new $controller();
            $controllerInstance->$action();
        } else {
            throw new \Exception("No route found for URL: $url");
        }
    }
}
