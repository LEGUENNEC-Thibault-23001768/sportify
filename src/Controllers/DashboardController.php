<?php

namespace Controllers;

use Core\View;
use Models\User;

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
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        echo $this->view->render('dashboard/index', ['user' => $user]);
    }

    public function showProfile() 
    {
        session_start();
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
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier si l'utilisateur est connecté
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            $userId = $_SESSION['user_id'];

            // Récupérer les données du formulaire et les nettoyer
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $birthDate = trim($_POST['birth_date']);
            $address = trim($_POST['address']);
            $phone = trim($_POST['phone']);

            // Validation simple (vous pouvez ajouter plus de règles si nécessaire)
            if (empty($firstName) || empty($lastName) || empty($email)) {
                $error = 'Les champs nom, prénom et email sont obligatoires.';
                $user = $this->userModel->getUserById($userId);
                echo $this->view->render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
                return;
            }

            // Préparer les données pour la mise à jour
            $data = [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'birth_date' => $birthDate,
                'address'    => $address,
                'phone'      => $phone,
            ];

            // Si l'utilisateur a fourni un nouveau mot de passe
            if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
                // Vérifier que le mot de passe actuel est correct
                if (!$this->userModel->verifyCurrentPassword($userId, $_POST['current_password'])) {
                    $_SESSION['error'] = 'Le mot de passe actuel est incorrect.';
                    header('Location: /dashboard/profile');
                    return;
                }

                // Vérifier que les nouveaux mots de passe correspondent
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    $_SESSION['error'] = 'Les deux mots de passe ne correspondent pas.';
                    header('Location: /dashboard/profile');
                    return;
                }

                // Ajouter le nouveau mot de passe à la mise à jour
                $data['new_password'] = $_POST['new_password'];
            }

            // Mettre à jour le profil utilisateur
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


    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }

}

?>