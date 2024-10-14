<?php

namespace Controllers;

use Core\View;
use Models\User;
use Models\Subscription;
class DashboardController
{
    private $view;
    private $userModel;

    public function __construct()
    {
        $this->view = new View();
        $this->userModel = new User();
    }

    public function showDashboard()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); // pas connecté
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        $subscriptionModel = new Subscription();
    
        $hasActiveSubscription = $subscriptionModel->hasActiveSubscription($userId);

        echo $this->view->render('dashboard/index', ['user' => $user, 'hasActiveSubscription' => $hasActiveSubscription]);
    }

    public function showProfile() 
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        echo $this->view->render('dashboard/profile/index', ['user' => $user]);
    }

    public function updateUserProfile()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            $userId = $_SESSION['user_id'];

            $subscriptionModel = new Subscription();
        
            $hasActiveSubscription = $subscriptionModel->hasActiveSubscription($userId);

            $this->view->render('dashboard', ['hasActiveSubscription' => $hasActiveSubscription]);

            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $birthDate = trim($_POST['birth_date']);
            $address = trim($_POST['address']);
            $phone = trim($_POST['phone']);

            if (empty($firstName) || empty($lastName) || empty($email)) {
                $error = 'Les champs nom, prénom et email sont obligatoires.';
                $user = $this->userModel->getUserById($userId);
                echo $this->view->render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
                return;
            }

            $data = [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'birth_date' => $birthDate,
                'address'    => $address,
                'phone'      => $phone,
            ];

            if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
                if (!$this->userModel->verifyCurrentPassword($userId, $_POST['current_password'])) {
                    $_SESSION['error'] = 'Le mot de passe actuel est incorrect.';
                    header('Location: /dashboard/profile');
                    return;
                }

                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    $_SESSION['error'] = 'Les deux mots de passe ne correspondent pas.';
                    header('Location: /dashboard/profile');
                    return;
                }

                $data['new_password'] = $_POST['new_password'];
            }

            if ($this->userModel->updateUserProfile($userId, $data)) {
                $_SESSION['success_message'] = 'Profil mis à jour avec succès.';
                header('Location: /dashboard/profile');
                exit();
            } else {
                $error = 'Échec de la mise à jour du profil.';
                $user = $this->userModel->getUserById($userId);
                echo $this->view->render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
            }
        } else {
            header('Location: /dashboard/profile');
            exit;
        }
    }
}