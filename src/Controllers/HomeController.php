<?php
// src/Controllers/HomeController.php

namespace Controllers;

use Core\View;
use Exception;

class HomeController
{
    /**
     * @return void
     */
    public function index(): void
    {
        echo View::renderWithLayout('home/index', 'layouts/main', [
            'title' => 'Accueil',
            'content' => 'Bienvenue sur notre site'
        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function notfound(): void
    {
        echo View::render('404/404', ['title' => 'Erreur page non trouvée']);
    }
}