<?php
// src/Controllers/HomeController.php

namespace Controllers;

use Core\View;

use Core\RouteProvider;
use Core\Router;

class HomeController implements RouteProvider {

    public static function routes(): void
    {
         Router::get('/',  self::class . '@index');
         Router::get('/404',  self::class . '@notfound');
    }

    public function index()
    {
        echo View::renderWithLayout('home/index', 'layouts/main', [
            'title' => 'Accueil',
            'content' => 'Bienvenue sur notre site'
        ]);
    }

    public function notfound() 
    {
        echo View::render('404/404', ['title' => 'Erreur page non trouvée']);
    }
}