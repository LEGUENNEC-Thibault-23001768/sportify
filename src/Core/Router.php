<?php

namespace Core;

class Router
{
    private $routes = [];

    public function addRoute($method, $url, $controller, $action)
    {
        $this->routes[$method][$url] = ['controller' => $controller, 'action' => $action];
    }


    public function getRoutes() {
        return $this->routes;
    }

    public function dispatch($url)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Remove query string from URL for matching
        $urlPath = parse_url($url, PHP_URL_PATH);
        
        if (!isset($this->routes[$method])) {
            throw new \Exception("No routes defined for method: $method");
        }

        foreach ($this->routes[$method] as $route => $handler) {
            if ($this->matchRoute($route, $urlPath)) {
                $controller = $handler['controller'];
                $action = $handler['action'];
                
                if (!class_exists($controller)) {
                    throw new \Exception("Controller not found: $controller");
                }
                
                try {
                    $controllerInstance = new $controller();
                } catch (\Throwable $e) {
                    throw new \Exception("Error creating controller instance: " . $e->getMessage());
                }
                
                if (!method_exists($controllerInstance, $action)) {
                    throw new \Exception("Action: $action not found in controller: $controller");
                }

                // Call the controller method without passing parameters
                try {
                    return $controllerInstance->$action();
                } catch (\Throwable $e) {
                    throw new \Exception("Error executing action '$action': " . $e->getMessage());
                }
            }
        }
        
        throw new \Exception("No route found for URL: $url with method: $method");
    }

    private function matchRoute($route, $url)
    {
        $pattern = preg_replace('/\/{(.*?)}/', '/([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        return preg_match($pattern, $url);
    }
}