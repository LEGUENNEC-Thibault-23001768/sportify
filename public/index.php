<?php

require_once dirname(__DIR__) . '/Autoloader.php';

ini_set('SMTP', 'smtp-sportify.alwaysdata.net');
ini_set('smtp_port', 587);
ini_set('sendmail_from', 'sportify@alwaysdata.net');


session_start();

use Core\Router;
use Core\Database;

$router = new Router();
$router->addRoute('GET','/', 'Controllers\HomeController', 'index');
$router->addRoute('GET', '/login', 'Controllers\AuthController', 'showLoginForm');
$router->addRoute('POST', '/login', 'Controllers\AuthController', 'login');

$router->addRoute('GET', '/verify-email', 'Controllers\AuthController', 'verifyEmail');

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