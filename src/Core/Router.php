<?php

namespace Core;

class Router
{
    private static $routes = [];

    public static function get($url, $handler, $middleware = null)
    {
        self::addRoute('GET', $url, $handler, $middleware);
    }

    public static function post($url, $handler, $middleware = null)
    {
        self::addRoute('POST', $url, $handler, $middleware);
    }

    public static function put($url, $handler, $middleware = null)
    {
        self::addRoute('PUT', $url, $handler, $middleware);
    }


    public static function delete($url, $handler, $middleware = null)
    {
        self::addRoute('DELETE', $url, $handler, $middleware);
    }

   public static function apiResource($url, $controller, $middleware = null)
    {
        self::get($url, "$controller@get", $middleware);
        self::get($url . '/{id}', "$controller@get", $middleware);
        self::post($url, "$controller@post", $middleware);
        self::put($url, "$controller@put", $middleware);
        self::put($url . '/{id}', "$controller@put", $middleware);
         self::delete($url . '/{id}', "$controller@delete", $middleware);
    }

    private static function addRoute($method, $url, $handler, $middleware = null)
    {
        self::$routes[$method][$url] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public static function dispatch($url)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $urlPath = parse_url($url, PHP_URL_PATH);

        if (!isset(self::$routes[$method])) {
            throw new \Exception("No routes defined for method: $method");
        }

        foreach (self::$routes[$method] as $route => $routeData) {
            $params = [];
            if (self::matchRoute($route, $urlPath, $params)) {
                // Apply middleware
                if (isset($routeData['middleware'])) {
                    $middlewares = is_array($routeData['middleware'])
                        ? $routeData['middleware']
                        : [$routeData['middleware']];

                    foreach ($middlewares as $middleware) {
                        if (!self::executeMiddleware($middleware)) {
                            return;
                        }
                    }
                }

                error_log("Matched Route: " . $route);
                error_log("Captured Parameters: " . print_r(array_values($params), true));

                $handler = $routeData['handler'];
                if (is_string($handler)) {
                    return self::invokeHandler($handler, array_values($params)); // Pass only parameter values
                } elseif (is_callable($handler)) {
                    return call_user_func_array($handler, array_values($params)); // Pass only parameter values
                } else {
                    throw new \Exception("Invalid route handler for route: $route");
                }
            }
        }

        throw new \Exception("No route found for URL: $urlPath with method: $method");
    }

    private static function matchRoute($route, $url, &$params)
    {
        $route = rtrim($route, '/');
        $url = rtrim($url, '/');


        if (strpos($route, '/*') !== false) {
            $pattern = preg_replace('/\/{(.*?)}/', '/(?<$1>[^/]+)', $route);

            $pattern = str_replace('/*', '(?<wildcard>.*)', $pattern);
            $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';
        } else {
            $pattern = preg_replace('/\/{(.*?)}/', '/(?<$1>[^/]+)', $route);
            $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';
        }



        if (preg_match($pattern, $url, $matches)) {
            $params = array_filter($matches, function ($key) {
                return is_string($key);
            }, ARRAY_FILTER_USE_KEY);

             if(strpos($route, '/*') !== false){
                 $wildcard_value = $matches['wildcard'] ?? null;
                 if($wildcard_value){
                      $params['wildcard'] = $wildcard_value;
                 }
              }

           return true;
        }

        return false;
    }

    private static function invokeHandler($handler, $params)
    {
        list($controllerName, $action) = explode('@', $handler);
        $controller = "Controllers\\$controllerName";

        if (!class_exists($controller)) {
            throw new \Exception("Controller not found: $controller");
        }

        $controllerInstance = new $controller();

        if ($controllerInstance instanceof \Core\APIController && !method_exists($controllerInstance, $action)) {
            return $controllerInstance->handleRequest($_SERVER['REQUEST_METHOD'], ...$params);
        } else {
            if (!method_exists($controllerInstance, $action)) {
                throw new \Exception("Action: $action not found in controller: $controller");
            }
            return call_user_func_array([$controllerInstance, $action], $params);
        }

    }

    private static function executeMiddleware($middleware)
    {
        if (is_callable($middleware)) {
            return $middleware();
        } else {
            throw new \Exception("Invalid middleware: " . $middleware);
        }
    }
}