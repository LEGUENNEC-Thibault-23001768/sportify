<?php
// src/Controllers/HomeController.php

namespace Controllers;

use Core\View;

class HomeController
{
    private $view;

    public function __construct()
    {
        $this->view = new View();
    }

    public function index()
    {
        echo $this->view->renderWithLayout('home/index', 'layouts/main', [
            'title' => 'Accueil',
            'content' => 'Bienvenue sur notre site'
        ]);
    }
}