<?php
require_once dirname(__DIR__) . '/Autoloader.php';

use Core\Config;
use Core\Router;
use Core\View;

ini_set('display_errors', 'Off');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

Config::load(dirname(__DIR__) . '/config.php');
View::init();
session_start();

Router::setup();


$url = $_SERVER['REQUEST_URI'];
try {
    header_remove("X-Powered-By");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

    Router::dispatch($url);
} catch (Exception $e) {
    error_log("Route Error: " . $e->getMessage());
    
    if (ini_get('display_errors')) {
        echo "<h2>Debug Error:</h2>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        http_response_code(404);
        header("Location: /404");
    }
    exit();
}