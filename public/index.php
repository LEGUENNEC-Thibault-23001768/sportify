<?php
require_once dirname(__DIR__) . '/Autoloader.php';

use Core\Config;
use Core\Router;
use Core\View;
use Core\Auth;


ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');
error_reporting(E_ALL);

Config::load(dirname(__DIR__) . '/config.php');
View::init();

session_start();

// --- Home and Error Routes ---
Router::get('/', 'HomeController@index');
Router::get('/404', 'HomeController@notfound');

// --- Authentication Routes ---
Router::get('/login', 'AuthController@showLoginForm');
Router::get('/register', 'AuthController@showLoginForm'); // Using the same form as login
Router::post('/login', 'AuthController@login');
Router::get('/verify-email', 'AuthController@verifyEmail');
Router::post('/register', 'AuthController@register');
Router::get('/logout', 'AuthController@logout', Auth::requireLogin());

// --- Dashboard and User Profile ---
Router::get('/dashboard', 'DashboardController@showDashboard', Auth::requireLogin());
Router::get('/dashboard/profile', 'DashboardController@showProfile', Auth::requireLogin());
Router::post('/dashboard/profile', 'DashboardController@updateUserProfile', Auth::requireLogin());

// --- Event Routes ---
Router::get('/dashboard/events', 'EventController@index', Auth::requireLogin());
Router::get('/api/events', 'EventController@getEvents');
Router::get('/api/events/{id}', 'EventController@show');
Router::post('/api/events', 'EventController@storeApi', [Auth::isAdmin(), Auth::isCoach()]);
Router::post('/api/events/join/{id}', 'EventController@join', Auth::requireLogin());
Router::post('/api/events/leave/{id}', 'EventController@leave', Auth::requireLogin());
Router::delete('/api/events/{id}', 'EventController@deleteApi', [Auth::isAdmin(), Auth::isCoach()]);
Router::post('/api/events/{id}/invite', 'EventController@sendInviteApi', [Auth::isAdmin(), Auth::isCoach()]);

// --- Booking Routes ---
Router::get('/dashboard/booking', 'BookingController@index', Auth::requireLogin());
Router::get('/dashboard/booking/create', 'BookingController@create', Auth::requireLogin());
Router::post('/dashboard/booking/store', 'BookingController@store', Auth::requireLogin());
Router::post('/dashboard/booking/{reservation_id}/delete', 'BookingController@delete', Auth::requireLogin());
Router::get('/dashboard/booking/{reservation_id}/edit', 'BookingController@edit', Auth::requireLogin());
Router::post('/dashboard/booking/{reservation_id}/update', 'BookingController@update', Auth::requireLogin());

// --- Admin Routes ---
Router::get('/dashboard/admin/users', 'DashboardController@manageUsers', Auth::isAdmin());
Router::delete('/dashboard/admin/users/delete/{id}', 'DashboardController@deleteUser', Auth::isAdmin());
Router::get('/api/users/{user_id}', 'DashboardController@getUserApi', Auth::isAdmin());
Router::post('/api/users/{user_id}', 'DashboardController@updateUserApi', Auth::isAdmin());
Router::get('/api/users/{user_id}/subscription', 'DashboardController@getUserSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription', 'DashboardController@updateUserSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription/cancel', 'DashboardController@cancelUserSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription/resume', 'DashboardController@resumeUserSubscription', Auth::isAdmin());
Router::get('/api/users', 'DashboardController@searchUsersApi', Auth::isAdmin());

// --- Team Routes ---
Router::get('/api/teams', 'TeamController@listTeams');
Router::post('/teams/{team_id}/add-member', 'TeamController@addParticipant', Auth::requireLogin());
Router::post('/teams/{team_id}/remove-member', 'TeamController@removeParticipant', Auth::requireLogin());


// --- Google Authentication ---
Router::get('/google', 'GoogleAuthController@login');
Router::get('/callback', 'GoogleAuthController@callback');

// --- Payment Routes ---
Router::post('/create-checkout-session', 'PaymentController@createCheckoutSession', Auth::requireLogin());
Router::get('/success', 'PaymentController@success', Auth::requireLogin());
Router::get('/invoices', 'PaymentController@listInvoices', Auth::requireLogin());
Router::post('/cancel-subscription', 'PaymentController@cancelSubscription', Auth::requireLogin());
Router::post('/resume-subscription', 'PaymentController@resumeSubscription', Auth::requireLogin());

// --- Password Reset ---
Router::get('/forgot-password', 'AuthController@showForgotPasswordForm');
Router::post('/forgot-password', 'AuthController@sendResetLink');
Router::get('/reset-password', 'AuthController@showResetPasswordForm');
Router::post('/reset-password', 'AuthController@resetPassword');

// --- Training Routes ---
Router::get('/dashboard/training', 'TrainingController@dashboard', Auth::requireLogin());
Router::get('/dashboard/training/start', 'TrainingController@start', Auth::requireLogin());
Router::get('/dashboard/training/generate', 'TrainingController@generate', Auth::requireLogin());
Router::get('/dashboard/training/edit', 'TrainingController@edit', Auth::requireLogin());
Router::post('/dashboard/training/update', 'TrainingController@update', Auth::requireLogin());
Router::get('/dashboard/training/train', 'TrainingController@train', Auth::requireLogin());

// --- API Training Routes ---
Router::post('/api/training/process-step', 'TrainingController@apiProcessStep', Auth::requireLogin());
Router::post('/api/training/generate', 'TrainingController@apiGenerate', Auth::requireLogin());

// --- Coach Booking Routes ---
Router::get('/dashboard/coach-bookings', 'CoachBookingController@index', Auth::requireLogin());
Router::get('/dashboard/coach-bookings/create', 'CoachBookingController@create', Auth::requireLogin());
Router::post('/dashboard/coach-bookings/store', 'CoachBookingController@store', Auth::requireLogin());
Router::post('/dashboard/coach-bookings/{booking_id}/delete', 'CoachBookingController@delete', Auth::requireLogin());
Router::get('/dashboard/coach-bookings/{booking_id}/edit', 'CoachBookingController@edit', Auth::requireLogin());
Router::post('/dashboard/coach-bookings/{booking_id}/update', 'CoachBookingController@update', Auth::requireLogin());
Router::get('/api/coaches', 'CoachBookingController@getCoaches'); // Ajout de la route API pour récupérer les coachs

use Controllers\TrainerController;

// Route pour obtenir les informations d'un coach
Router::get('/api/trainers/{id}', function($id) {
    $controller = new TrainerController();
    $controller->show($id);
});

Router::post('/api/reservation', 'TrainerController@saveReservation', Auth::requireLogin());
Router::get('/api/reservations/{coachId}', 'TrainerController@getReservations');







// --- CONTENT LOADER ---
Router::get('/dashboard/content/{category}/*', 'DashboardController@contentLoader', Auth::requireLogin());

$url = $_SERVER['REQUEST_URI'];
try {
    Router::dispatch($url);
} catch (Exception $e) {
    //http_response_code(404);
    //header("Location: /404");
    //exit();
    echo "Page not found: " . $e->getMessage();
}
