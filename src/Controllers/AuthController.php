<?php

namespace Controllers;

use Models\User;
use Core\View;

class AuthController
{
    public function showLoginForm()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        echo View::render('auth/login');
    }

    public function showRegisterForm()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        echo View::render('auth/register');
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
        } else {
            header('Location: /login');
            exit;
        }
    }

    public function logout()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);

            if (empty($email) || empty($password) || empty($confirmPassword)) {
                $error = 'Tous les champs sont obligatoires.';
                echo View::render('auth/register', ['error' => $error]);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Veuillez entrer un email valide.';
                echo View::render('auth/register', ['error' => $error]);
                return;
            }

            if ($password !== $confirmPassword) {
                $error = 'Les mots de passe ne correspondent pas.';
                echo View::render('auth/register', ['error' => $error]);
                return;
            }

            if (User::findByEmail($email)) {
                $error = 'Cet email est déjà utilisé.';
                echo View::render('auth/register', ['error' => $error]);
                return;
            }

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

            if (User::create($newUser)) {
                $message = "Un email de vérification a été envoyé à votre adresse. Veuillez vérifier votre boîte de réception.";
                error_log($message);
                echo View::render('auth/register', ['message' => $message]);
            } else {
                $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                echo View::render('auth/register', ['error' => $error]);
            }
        }
    }

    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        if (User::verifyEmail($token)) {
            $message = "Votre email a été vérifié avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            $message = "Le lien de vérification est invalide ou a expiré.";
        }
        View::render('auth/register', ['message' => $message]);
    }

    public function sendResetLink()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    }

    public function showForgotPasswordForm()
    {
        echo View::render('auth/forgot-password');
    }

    public function showResetPasswordForm()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            header('Location: /login');
            exit;
        }
        echo View::render('auth/reset-password', ['token' => $token]);
    }

    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}