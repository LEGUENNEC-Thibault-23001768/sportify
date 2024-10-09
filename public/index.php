<?php

require_once dirname(__DIR__) . '/Autoloader.php';

session_start();

use Core\Router;
use Core\Database;

$router = new Router();
$router->addRoute('GET','/', 'Controllers\HomeController', 'index');
$router->addRoute('GET', '/login', 'Controllers\AuthController', 'showLoginForm');
$router->addRoute('POST', '/login', 'Controllers\AuthController', 'login');
$router->addRoute('GET', '/register', 'Controllers\AuthController', 'showRegisterForm');
$router->addRoute('POST', '/register', 'Controllers\AuthController', 'register');

// Récupérez l'URL demandée
$url = $_SERVER['REQUEST_URI'];

try {

    $router->dispatch($url);
} catch (Exception $e) {
    // Gérez les erreurs (par exemple, affichez une page 404)
    echo "Page not found: " . $e->getMessage();
}