<?php

namespace Controllers;

use Core\View;
use Core\Database;
use PDO;

class LoginController
{
    private $view;
    private $db;

    public function __construct()
    {
        $this->view = new View();
        $this->db = Database::getInstance()->getConnection();
    }

    public function showLoginForm()
    {
        // Render the login form view
        echo $this->view->renderWithLayout('login/form', 'layouts/main', [
            'title' => 'Login'
        ]);
    }

    public function login()
    {
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Validate input
            if (empty($email) || empty($password)) {
                echo $this->view->renderWithLayout('login/form', 'layouts/main', [
                    'title' => 'Login',
                    'error' => 'Both fields are required.'
                ]);
                return;
            }

            // Check if the user exists
            $stmt = $this->db->prepare('SELECT * FROM MEMBER WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Login successful, set session
                session_start();
                $_SESSION['user_id'] = $user['member_id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

                // Redirect to dashboard or welcome page
                header('Location: /dashboard');
                exit();
            } else {
                // Invalid credentials
                echo $this->view->renderWithLayout('login/form', 'layouts/main', [
                    'title' => 'Login',
                    'error' => 'Invalid email or password.'
                ]);
            }
        } else {
            // If not a POST request, show the login form again
            $this->showLoginForm();
        }
    }

    public function logout()
    {
        // Destroy the session to log the user out
        session_start();
        session_unset();
        session_destroy();

        // Redirect to the login page
        header('Location: /login');
        exit();
    }
}
