<?php
require_once dirname(__DIR__) . '/Autoloader.php';

use Core\Config;
use Core\Router;
use Core\View;
use Core\Auth;

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


// --- Event Routes ---
Router::get('/api/events', 'EventController@getEvents',Auth::requireLogin());  
Router::get('/api/events/{id}', 'EventController@show');
Router::post('/api/events', 'EventController@storeApi', [Auth::isAdmin()]);
Router::post('/api/events/join/{id}', 'EventController@postJoin', Auth::requireLogin());
Router::post('/api/events/leave/{id}', 'EventController@postLeave', Auth::requireLogin());
Router::delete('/api/events/{id}', 'EventController@deleteApi', [Auth::isAdmin(), Auth::isCoach()]);
Router::post('/api/events/{id}/invite', 'EventController@postSendInviteApi', [Auth::isAdmin(), Auth::isCoach()]);

// --- Booking Routes ---
Router::apiResource('/api/booking', 'BookingController', Auth::requireLogin());

// --- Dashboard and User Profile ---
Router::get('/dashboard', 'DashboardController@showDashboard', Auth::requireLogin());

//Router::get('/dashboard/profile', 'UserController@showProfile', Auth::requireLogin()); //  <---  Add this to display profile
Router::put('/api/profile', 'UserController@updateProfile', Auth::requireLogin()); // Update current user's profile


// --- User Routes (API) ---
Router::apiResource('/api/users', 'UserController', Auth::isAdmin());
Router::get('/api/users/{user_id}/subscription', 'UserController@getSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription', 'UserController@updateSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription/cancel', 'UserController@cancelSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription/resume', 'UserController@resumeSubscription', Auth::isAdmin());

// --- Admin Routes ---
/*Router::delete('/dashboard/admin/users/delete/{id}', 'DashboardController@deleteUser', Auth::isAdmin());
Router::get('/api/users/{user_id}', 'DashboardController@getUserApi', Auth::isAdmin());
Router::post('/api/users/{user_id}', 'DashboardController@updateUserApi', Auth::isAdmin());
Router::get('/api/users/{user_id}/subscription', 'DashboardController@getUserSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription', 'DashboardController@updateUserSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription/cancel', 'DashboardController@cancelUserSubscription', Auth::isAdmin());
Router::post('/api/users/{user_id}/subscription/resume', 'DashboardController@resumeUserSubscription', Auth::isAdmin());
Router::get('/api/users', 'DashboardController@searchUsersApi', Auth::isAdmin());
*/

// --- Team Routes ---
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


// --- API Coach reservation
Router::get('/api/trainers/{id}', 'TrainerController@show', Auth::requireLogin());
Router::post('/api/reservation', 'TrainerController@saveReservation', Auth::requireLogin());
Router::get('/api/reservations/{coachId}', 'TrainerController@getReservations');
Router::delete('/api/reservation/delete/{reservationId}', 'TrainerController@deleteReservation', Auth::requireLogin());
Router::post('/api/reservation/update/{reservationId}', 'TrainerController@updateReservation', Auth::requireLogin());


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
