<?php

namespace Controllers;

use Models\User;
use Core\View;
use Core\Router;
use Core\RouteProvider;
use Core\Auth;

class AuthController implements RouteProvider
{

    public static function routes(): void
    {
        Router::get('/login', self::class . '@showLoginForm');
        Router::get('/register', self::class . '@showLoginForm');
        Router::get('/verify-email', self::class . '@verifyEmail');
        Router::get('/logout', self::class . '@logout', Auth::requireLogin());
        
        Router::get('/forgot-password', self::class . '@showForgotPasswordForm');
        Router::get('/reset-password', self::class . '@showResetPasswordForm');

        Router::post('/login', self::class . '@login');
        Router::post('/register', self::class . '@register');
        
        Router::post('/forgot-password', self::class . '@sendResetLink');
        Router::post('/reset-password', self::class . '@resetPassword');
    }

    public function showLoginForm()
    {
        echo View::render('auth/login');
    }

    public function showRegisterForm()
    {
        echo View::render('auth/register');
    }

    public function login()
    {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Format d'email invalide.";
            header('Location: /login');
            exit;
        }

        
        $password = $_POST['password'] ?? '';
        $user = User::login($email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['member_id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: /dashboard');
            exit;
        } else {
            echo View::render('auth/login', ['error' => 'Email ou mot de passe incorrect.']);
        }
    }

    public function logout()
    {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', 1,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function register()
    {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirm_password']);

        if (empty($email) || empty($password) || empty($confirmPassword)) {
            $error = 'Tous les champs sont obligatoires.';
            echo View::render('auth/login', ['error' => $error]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez entrer un email valide.';
            echo View::render('auth/login', ['error' => $error]);
            return;
        }

        if ($password !== $confirmPassword) {
            $error = 'Les mots de passe ne correspondent pas.';
            echo View::render('auth/login', ['error' => $error]);
            return;
        }

        if (User::findByEmail($email)) {
            $error = 'Cet email est déjà utilisé.';
            echo View::render('auth/login', ['error' => $error]);
            return;
        }

        $newUser = [
            'email' => $email,
            'password' => $password,
            'first_name' => 'DefaultFirst', 
            'last_name' => 'DefaultLast',   
            'birth_date' => null,           
            'address' => null,              
            'phone' => null                 
        ];

        if (User::create($newUser)) {
            $message = "Un email de vérification a été envoyé à votre adresse. Veuillez vérifier votre boîte de réception.";
            echo View::render('auth/login', ['message' => $message]);
        } else {
            $error = "Erreur lors de l'inscription. Veuillez réessayer.";
            echo View::render('auth/login', ['error' => $error]);
        }
    }

    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        $message = '';
        if (User::verifyEmail($token)) {
            $message = "Votre email a été vérifié avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            $message = "Le lien de vérification est invalide ou a expiré.";
        }
        echo View::render('auth/login', ['message' => $message]);
    }

    public function sendResetLink()
    {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo View::render('auth/forgot-password', ['error' => "Format d'email invalide."]);
            return;
        }

        $user = User::findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            if (User::storeResetToken($email, $token)) {
                User::sendPasswordResetEmail($email, $token);
                echo View::render('auth/forgot-password', ['message' => "Un lien de réinitialisation a été envoyé à votre adresse email."]);
            } else {
                echo View::render('auth/forgot-password', ['error' => "Une erreur est survenue. Veuillez réessayer."]);
            }
        } else {
            echo View::render('auth/forgot-password', ['error' => "Aucun compte trouvé avec cet email."]);
        }
    }

    public function showForgotPasswordForm()
    {
        echo View::renderWithLayout('auth/forgot-password', 'layouts/main', [
            'title' => "Mot de passe oublié",
            'css' => "auth"
        ]);
    }

    public function showResetPasswordForm()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            header('Location: /login');
            exit;
        }
        echo View::renderWithLayout('auth/reset-password', 'layouts/main', [
            'title' => "Réinitialiser le mot de passe",
            "css" => "auth",
            'token' => $token]);
    }

    public function resetPassword()
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($password !== $confirmPassword) {
            echo View::render('auth/reset-password', ['error' => "Les mots de passe ne correspondent pas.", 'token' => $token]);
            return;
        }

        if (User::resetPassword($token, $password)) {
            echo View::render('auth/login', ['message' => "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter."]);
        } else {
            echo View::render('auth/reset-password', ['error' => "Le lien de réinitialisation est invalide ou a expiré.", 'token' => $token]);
        }
    }
}