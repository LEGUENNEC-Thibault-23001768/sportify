<?php
require_once dirname(__DIR__) . '/Autoloader.php';

use Core\Config;
use Core\Router;
use Core\View;

Config::load(dirname(__DIR__) . '/config.php');
View::init();

session_start();

Router::setup();

// --- Event Routes ---
/*
Router::get('/api/events', 'EventController@getEvents',Auth::requireLogin());  
Router::get('/api/events/{id}', 'EventController@show');
Router::post('/api/events', 'EventController@storeApi', [Auth::isAdmin()]);
Router::post('/api/events/join/{id}', 'EventController@postJoin', Auth::requireLogin());
Router::post('/api/events/leave/{id}', 'EventController@postLeave', Auth::requireLogin());
Router::delete('/api/events/{id}', 'EventController@deleteApi', [Auth::isAdmin(), Auth::isCoach()]);
Router::post('/api/events/{id}/invite', 'EventController@postSendInviteApi', [Auth::isAdmin(), Auth::isCoach()]);
*/

// --- Team Routes ---
//Router::post('/teams/{team_id}/add-member', 'TeamController@addParticipant', Auth::requireLogin());
//Router::post('/teams/{team_id}/remove-member', 'TeamController@removeParticipant', Auth::requireLogin());

$url = $_SERVER['REQUEST_URI'];
try {
    Router::dispatch($url);
} catch (Exception $e) {
    //http_response_code(404);
    //header("Location: /404");
    //exit();
    echo "Page not found: " . $e->getMessage();
}
