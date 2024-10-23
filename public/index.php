<?php
require_once dirname(__DIR__) . '/Autoloader.php';

use Core\Config;
use Core\Router;

Config::load(dirname(__DIR__) . '/config.php');

session_start();

$router = new Router();
$router->get('/', 'HomeController@index');
$router->get('/404', 'HomeController@notfound');

$router->get( '/login', 'AuthController@showLoginForm');
$router->post( '/login', 'AuthController@login');
$router->get( '/verify-email', 'AuthController@verifyEmail');
$router->get( '/register', 'AuthController@showRegisterForm');
$router->post( '/register', 'AuthController@register');
$router->get( '/logout', 'AuthController@logout');

$router->get( '/dashboard/events', 'EventController@index');
$router->get( '/dashboard/events/show', 'EventController@show');
$router->get( '/dashboard/events/create', 'EventController@create');
$router->post( '/dashboard/events/{event_id}/delete', 'EventController@ðelete');
$router->post( '/dashboard/events/store', 'EventController@store');
$router->post( '/dashboard/events/{event_id}/delete', 'EventController@delete');
$router->get( '/dashboard/events/{event_id}', 'EventController@show');
$router->post( '/dashboard/events/{event_id}/join', 'EventController@join');


$router->addRoute('GET', '/dashboard/admin/users', 'Controllers\DashboardController', 'manageUsers');
$router->addRoute('GET', '/dashboard/admin/users/delete', 'Controllers\DashboardController', 'deleteUser');
$router->addRoute('GET', '/dashboard/admin/users/edit', 'Controllers\DashboardController', 'editUserProfile');
$router->addRoute('POST', '/dashboard/admin/users/edit', 'Controllers\DashboardController', 'editUserProfile');



$router->post( '/teams/{team_id}/add-member', 'TeamController@addParticipant');
$router->post( '/teams/{team_id}/remove-member', 'TeamController@removeParticipant');

$router->get( '/dashboard', 'DashboardController@showDashboard');
$router->get( '/dashboard/profile', 'DashboardController@showProfile');
$router->post( '/dashboard/profile', 'DashboardController@updateUserProfile');

$router->get( '/google', 'GoogleAuthController@login' );
$router->get( '/callback', 'GoogleAuthController@callback');

$router->post( '/create-checkout-session', 'PaymentController@createCheckoutSession');
$router->get( '/success', 'PaymentController@success');
$router->get( '/invoices', 'PaymentController@listInvoices');
$router->post('/cancel-subscription', 'PaymentController@cancelSubscription');

$router->get( '/forgot-password', 'AuthController@showForgotPasswordForm');
$router->post( '/forgot-password', 'AuthController@sendResetLink');
$router->get( '/reset-password', 'AuthController@showResetPasswordForm');
$router->post( '/reset-password', 'AuthController@resetPassword');



$url = $_SERVER['REQUEST_URI'];
try {
    $router->dispatch($url);
} catch (Exception $e) {
    //http_response_code(404);
    //header("Location: /404");
    //exit();
    echo "Page not found: " . $e->getMessage();
}
