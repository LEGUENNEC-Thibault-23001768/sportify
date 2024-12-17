<?php
require_once dirname(__DIR__) . '/Autoloader.php';

use Core\Config;
use Core\Router;
use Core\View;

Config::load(dirname(__DIR__) . '/config.php');
View::init();

session_start();

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/404', 'HomeController@notfound');

$router->get( '/login', 'AuthController@showLoginForm');
$router->get( '/register', 'AuthController@showLoginForm'); // on fait un hack.

$router->post( '/login', 'AuthController@login');
$router->get( '/verify-email', 'AuthController@verifyEmail');
$router->post( '/register', 'AuthController@register');
$router->get( '/logout', 'AuthController@logout');

$router->get( '/dashboard/events', 'EventController@index');


$router->get('/api/events', 'EventController@getEvents');
$router->get('/api/events/{id}', 'EventController@show');
$router->post('/api/events', 'EventController@storeApi');
$router->post('/api/events/join/{id}', 'EventController@join');
$router->post('/api/events/leave/{id}', 'EventController@leave');
$router->delete('/api/events/{id}', 'EventController@deleteApi');
$router->post('/api/events/{id}/invite', 'EventController@sendInviteApi');



$router->get('/dashboard/booking', 'BookingController@index');
$router->get('/dashboard/booking/create', 'BookingController@create');
$router->post('/dashboard/booking/store', 'BookingController@store');
$router->post( '/dashboard/booking/{reservation_id}/delete', 'BookingController@delete');
$router->get('/dashboard/booking/{reservation_id}/edit', 'BookingController@edit');
$router->post('/dashboard/booking/{reservation_id}/update', 'BookingController@update');


$router->get( '/dashboard/admin/users', 'DashboardController@manageUsers');
$router->get( '/dashboard/admin/users/delete', 'DashboardController@deleteUser');
$router->get( '/dashboard/admin/users/edit', 'DashboardController@editUserProfile');
$router->post( '/dashboard/admin/users/edit', 'DashboardController@editUserProfile');



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

$router->get('/dashboard/training/start', 'TrainingController@start');
$router->get('/dashboard/training/step/{step}', 'TrainingController@step');
$router->post('/dashboard/training/step/{step}', 'TrainingController@step');
$router->get('/dashboard/training/generate', 'TrainingController@generate');
$router->get('/dashboard/training', 'TrainingController@dashboard');
$router->get('/dashboard/training/edit', 'TrainingController@edit');
$router->post('/dashboard/training/edit', 'TrainingController@edit');
$router->get('/dashboard/load-content', 'DashboardController@loadContent');



$url = $_SERVER['REQUEST_URI'];
try {
    $router->dispatch($url);
} catch (Exception $e) {
    //http_response_code(404);
    //header("Location: /404");
    //exit();
    echo "Page not found: " . $e->getMessage();
}
