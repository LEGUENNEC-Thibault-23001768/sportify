<?php

namespace Controllers;

use Models\User;
use Core\View;

class AuthController
{
    private $userModel;
    private $view;

    public function __construct()
    {
        $this->userModel = new User();
        $this->view = new View();
    }

    public function showLoginForm()
    {
        echo $this->view->render('auth/login');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_message'] = "Format d'email invalide.";
                header('Location: /login');
                exit;
            }
            
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['member_id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                header('Location: /dashboard');
                exit;
            } else {
                echo $this->view->render('auth/login', ['error' => 'Email ou mot de passe incorrect.']);
            }
        } else {
            header('Location: /login');
            exit;
        }
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function showRegisterForm()
    {
        echo $this->view->render('auth/register');
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lastName = $_POST['lastName'] ?? '';
            $firstName = $_POST['firstName'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $birthDate = $_POST['birthDate'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';

            // Ici, vous devriez ajouter une validation des données

            $result = $this->userModel->register($lastName, $firstName, $email, $password, $birthDate, $address, $phone);

            if ($result) {
                header('Location: /login?registered=1');
                exit;
            } else {
                echo $this->view->render('auth/register', ['error' => 'L\'inscription a échoué. Veuillez réessayer.']);
            }
        } else {
            header('Location: /register');
            exit;
        }
    }
    /**
    public function showResetPasswordForm()
    {
        echo $this->view->render('auth/reset-password');
    }

    public function resetPassword()
    {
        // Implémentez ici la logique de réinitialisation du mot de passe
        // Cela pourrait inclure l'envoi d'un email avec un lien de réinitialisation
        // et la mise à jour du mot de passe dans la base de données
    }*/
}
