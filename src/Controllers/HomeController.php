<?php
// src/Controllers/HomeController.php

namespace Controllers;

use Core\View;

class HomeController
{
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