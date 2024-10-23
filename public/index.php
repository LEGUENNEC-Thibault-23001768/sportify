<?php
require_once dirname(__DIR__) . '/Autoloader.php';

use Core\Config;
use Core\Router;

Config::load(dirname(__DIR__) . '/config.php');

session_start();

$router = new Router();
$router->addRoute('GET','/', 'Controllers\HomeController', 'index');
$router->addRoute('GET','/404', 'Controllers\HomeController', 'notfound');

$router->addRoute('GET', '/login', 'Controllers\AuthController', 'showLoginForm');
$router->addRoute('POST', '/login', 'Controllers\AuthController', 'login');
$router->addRoute('GET', '/verify-email', 'Controllers\AuthController', 'verifyEmail');
$router->addRoute('GET', '/register', 'Controllers\AuthController', 'showRegisterForm');
$router->addRoute('POST', '/register', 'Controllers\AuthController', 'register');
$router->addRoute('GET', '/logout', 'Controllers\AuthController', 'logout');

$router->addRoute('GET', '/dashboard/events', 'Controllers\EventController', 'index');
$router->addRoute('GET', '/dashboard/events/show', 'Controllers\EventController', 'show');
$router->addRoute('GET', '/dashboard/events/create', 'Controllers\EventController', 'create');
$router->addRoute('POST', '/dashboard/events/{event_id}/delete', 'Controllers\EventController', 'delete');
$router->addRoute('POST', '/dashboard/events/store', 'Controllers\EventController', 'store');
$router->addRoute('POST', '/dashboard/events/{event_id}/delete', 'Controllers\EventController', 'delete');
$router->addRoute('GET', '/dashboard/events/{event_id}', 'Controllers\EventController', 'show');
$router->addRoute('POST', '/dashboard/events/{event_id}/join', 'Controllers\EventController', 'join');


$router->addRoute('GET', '/dashboard/admin/users', 'Controllers\DashboardController', 'manageUsers');
$router->addRoute('GET', '/dashboard/admin/users/delete', 'Controllers\DashboardController', 'deleteUser');
$router->addRoute('GET', '/dashboard/admin/users/edit', 'Controllers\DashboardController', 'editUserProfile');
$router->addRoute('POST', '/dashboard/admin/users/edit', 'Controllers\DashboardController', 'editUserProfile');



$router->addRoute('POST', '/teams/{team_id}/add-member', 'Controllers\TeamController', 'addParticipant');
$router->addRoute('POST', '/teams/{team_id}/remove-member', 'Controllers\TeamController', 'removeParticipant');

$router->addRoute('GET', '/dashboard', 'Controllers\DashboardController', 'showDashboard');
$router->addRoute('GET', '/dashboard/profile', 'Controllers\DashboardController', 'showProfile');
$router->addRoute('POST', '/dashboard/profile', 'Controllers\DashboardController', 'updateUserProfile');


$router->addRoute('GET', '/google', 'Controllers\GoogleAuthController' , 'login');
$router->addRoute('GET', '/callback', 'Controllers\GoogleAuthController', 'callback');


$router->addRoute('POST', '/create-checkout-session', 'Controllers\PaymentController', 'createCheckoutSession');
$router->addRoute('GET', '/success', 'Controllers\PaymentController', 'success');


$router->addRoute('GET', '/forgot-password', 'Controllers\AuthController', 'showForgotPasswordForm');
$router->addRoute('POST', '/forgot-password', 'Controllers\AuthController', 'sendResetLink');
$router->addRoute('GET', '/reset-password', 'Controllers\AuthController', 'showResetPasswordForm');
$router->addRoute('POST', '/reset-password', 'Controllers\AuthController', 'resetPassword');



$url = $_SERVER['REQUEST_URI'];
try {
    $router->dispatch($url);
} catch (Exception $e) {
    echo "Page not found: " . $e->getMessage();
}
