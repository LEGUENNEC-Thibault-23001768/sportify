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
    public function showRegisterForm()
    {
        echo $this->view->render('auth/register');
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


    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);

            // Validation de base
            if (empty($email) || empty($password) || empty($confirmPassword)) {
                $error = 'Tous les champs sont obligatoires.';
                $this->view->render('auth/register', ['error' => $error]);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Veuillez entrer un email valide.';
                $this->view->render('auth/register', ['error' => $error]);
                return;
            }

            if ($password !== $confirmPassword) {
                $error = 'Les mots de passe ne correspondent pas.';
                $this->view->render('auth/register', ['error' => $error]);
                return;
            }

            // Vérification si l'email existe déjà
            if ($this->userModel->findByEmail($email)) {
                $error = 'Cet email est déjà utilisé.';
                $this->view->render('auth/register', ['error' => $error]);
                return;
            }

            // Hachage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $newUser = [
                'email' => $email,
                'password' => $hashedPassword,
                'first_name' => 'DefaultFirst', 
                'last_name' => 'DefaultLast',   
                'birth_date' => null,           
                'address' => null,              
                'phone' => null                 
            ];

            if ($this->userModel->create($newUser)) {
                $message = "Un email de vérification a été envoyé à votre adresse. Veuillez vérifier votre boîte de réception.";
                error_log($message);
                $this->view->render('auth/register', ['message' => $message]);

                exit();
            } else {
                $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                $this->view->render('auth/register', ['error' => $error]);
            }
        }
    }

    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        if ($this->userModel->verifyEmail($token)) {
            $message = "Votre email a été vérifié avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            $message = "Le lien de vérification est invalide ou a expiré.";
        }
        $this->view->render('auth/register', ['message' => $message]);
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
