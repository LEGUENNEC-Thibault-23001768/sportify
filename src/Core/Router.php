<?php

namespace Core;

use Exception;

class Router
{
    private static $routes = [];
    private static $controllers = [];
    private static $initialized = false;
    private static $baseNamespace = "Controllers\\";

    public static function apiResource($url, $controller, $middleware = null)
    {
        self::get($url, "$controller@get", $middleware);
        self::get($url . '/{id}', "$controller@get", $middleware);
        self::post($url, "$controller@post", $middleware);
        self::put($url, "$controller@put", $middleware);
        self::put($url . '/{id}', "$controller@put", $middleware);
        self::delete($url . '/{id}', "$controller@delete", $middleware);
    }

    public static function get($url, $handler, $middleware = null)
    {
        self::addRoute('GET', $url, $handler, $middleware);
    }

    private static function addRoute($method, $url, $handler, $middleware = null)
    {
        self::$routes[$method][$url] = ['handler' => $handler, 'middleware' => $middleware];
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

    public static function dispatch($url)
    {
        if (!self::$initialized) {
            self::setup();
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $urlPath = parse_url($url, PHP_URL_PATH);

        if (!isset(self::$routes[$method])) {
            throw new Exception("No routes defined for method: $method");
        }

        // 1. Check for exact match first
        if (isset(self::$routes[$method][$urlPath])) {
            $routeData = self::$routes[$method][$urlPath];
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
            error_log("Matched Route: " . $urlPath);
            error_log("Captured Parameters: " . print_r([], true));
            $handler = $routeData['handler'];
            if (is_string($handler)) {
                return self::invokeHandler($handler, [], $method);
            } elseif (is_callable($handler)) {
                return call_user_func_array($handler, []);
            } else {
                throw new Exception("Invalid route handler for route: $urlPath");
            }
        }


        // 2. Check for parameterized match
        foreach (self::$routes[$method] as $route => $routeData) {
            $params = [];
            if (self::matchRoute($route, $urlPath, $params)) {
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
                    return self::invokeHandler($handler, array_values($params), $method);
                } elseif (is_callable($handler)) {
                    return call_user_func_array($handler, array_values($params));
                } else {
                    throw new Exception("Invalid route handler for route: $route");
                }
            }
        }
        throw new Exception("No route found for URL: $urlPath with method: $method");
    }

    public static function setup()
    {
        if (self::$initialized) return;
        self::loadControllers();
        self::$initialized = true;
    }

    private static function loadControllers()
    {
        $files = glob(__DIR__ . '/../Controllers/*.php');

        if ($files) {
            foreach ($files as $file) {
                $class = self::$baseNamespace . pathinfo($file, PATHINFO_FILENAME);
                if (class_exists($class) && in_array(\Core\RouteProvider::class, class_implements($class))) {
                    $class::routes();
                    self::$controllers[] = $class;
                }
            }
        }
    }

    private static function executeMiddleware($middleware)
    {
        if (is_callable($middleware)) {
            return $middleware();
        } else {
            throw new Exception("Invalid middleware: " . $middleware);
        }
    }

    private static function invokeHandler($handler, $params, $method)
    {
        list($controllerName, $action) = explode('@', $handler);
        $controller = $controllerName;

        if (!class_exists($controller)) {
            throw new Exception("Controller not found: $controller");
        }

        $controllerInstance = new $controller();
        if ($controllerInstance instanceof \Core\APIController) {
            if (method_exists($controllerInstance, $action)) {
                return call_user_func_array([$controllerInstance, $action], $params);
            }
            if (method_exists($controllerInstance, strtolower($method))) {
                return call_user_func_array([$controllerInstance, strtolower($method)], $params);
            }

            $response = new APIResponse();
            return $response->setStatusCode(405)->setData(['error' => 'Method not allowed.'])->send();
        } else {
            if (!method_exists($controllerInstance, $action)) {
                throw new Exception("Action: $action not found in controller: $controller");
            }
            return call_user_func_array([$controllerInstance, $action], $params);
        }
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
            if (strpos($route, '/*') !== false) {
                $wildcard_value = $matches['wildcard'] ?? null;
                if ($wildcard_value) {
                    $params['wildcard'] = $wildcard_value;
                }
            }
            return true;
        }

        return false;
    }
}