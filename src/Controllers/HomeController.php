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
       Router::get('/{page}', self::class . '@handlePage');
    }

    public function index()
    {
        echo View::renderWithLayout('home/index', 'layouts/main', [
            'title' => 'Accueil',
        ]);
    }

    public function handlePage($page)
    {
        $viewPath = "home/{$page}";
        $css = "home";

        $title = ucfirst($page);

        try {
            if(View::exists($viewPath)){
             echo View::renderWithLayout($viewPath, 'layouts/main', [
                 'title' => $title,
                 'css' => $css
             ]);
             } else {
                  $this->notfound();
              }
        } catch (\Exception $e) {
            $this->notfound();
        }
    }
    public function notfound() 
    {
        echo View::render('404/404', ['title' => 'Erreur page non trouvée']);
    }
}