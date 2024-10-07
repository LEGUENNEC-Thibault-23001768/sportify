<?php

require_once dirname(__DIR__) . '/Autoloader.php';

use Core\Router;
use Core\Database;

// Configurez vos routes
$router = new Router();
$router->addRoute('/', 'Controllers\HomeController', 'index');
$router->addRoute('/members', 'Controllers\MemberController', 'list');
// Ajoutez d'autres routes selon vos besoins

// Récupérez l'URL demandée
$url = $_SERVER['REQUEST_URI'];

try {
    // Dispatchez la requête
    $router->dispatch($url);
} catch (Exception $e) {
    // Gérez les erreurs (par exemple, affichez une page 404)
    echo "Page not found";
}