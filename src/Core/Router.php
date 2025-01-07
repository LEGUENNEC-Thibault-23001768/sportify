<?php

namespace Core;

use Exception;

class Router
{
    private array $routes = [];

    /**
     * @param $url
     * @param $handler
     * @return void
     */
    public function get($url, $handler): void
    {
        $this->addRoute('GET', $url, $handler);
    }

    /**
     * @param $method
     * @param $url
     * @param $handler
     * @return void
     */
    private function addRoute($method, $url, $handler): void
    {
        $this->routes[$method][$url] = $handler;
    }

    /**
     * @param $url
     * @param $handler
     * @return void
     */
    public function post($url, $handler): void
    {
        $this->addRoute('POST', $url, $handler);
    }

    /**
     * @param $url
     * @param $handler
     * @return void
     */
    public function delete($url, $handler): void
    {
        $this->addRoute('DELETE', $url, $handler);
    }

    /**
     * @param $url
     * @return mixed
     * @throws Exception
     */
    public function dispatch($url): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $urlPath = parse_url($url, PHP_URL_PATH);

        if (!isset($this->routes[$method])) {
            throw new Exception("No routes defined for method: $method");
        }

        foreach ($this->routes[$method] as $route => $handler) {
            $params = [];
            if ($this->matchRoute($route, $urlPath, $params)) {
                if (is_string($handler)) {
                    list($controller, $action) = explode('@', $handler);
                    $controller = "Controllers\\$controller";

                    if (!class_exists($controller)) {
                        throw new Exception("Controller not found: $controller");
                    }

                    $controllerInstance = new $controller();

                    if (!method_exists($controllerInstance, $action)) {
                        throw new Exception("Action: $action not found in controller: $controller");
                    }

                    return $controllerInstance->$action(...$params);
                } elseif (is_callable($handler)) {
                    return $handler(...$params);
                } else {
                    throw new Exception("Invalid route handler");
                }
            }
        }

        throw new Exception("No route found for URL: $url with method: $method");
    }

    /**
     * @param $route
     * @param $url
     * @param $params
     * @return bool
     */
    private function matchRoute($route, $url, &$params): bool
    {
        $pattern = preg_replace('/\/{(.*?)}/', '/([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $url, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }
        return false;
    }
}