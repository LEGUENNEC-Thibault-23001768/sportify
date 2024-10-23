<?php

namespace Controllers;

use Core\View;
use Models\Member;
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

        if ($user) {
            $memberId = $user['member_id']; // Récupération de member_id
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

        //echo $userId;

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


    public function manageUsers() {

        $user_id = $_SESSION['user_id'];

        $membreModel = new User();
        $membre = $membreModel->find($user_id);

        if ($membre['status'] !== 'admin') {
            header("Location: /dashboard");
            exit;
        }
    
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

        $userModel = new User();
        if (!empty($searchTerm)) {
            $users = $userModel->searchUsers($searchTerm);
        } else {
            $users = $userModel->getAllUsers();
        }
        echo $this->view->render('dashboard/admin/users/index', ['users' => $users, 'membre' => $membre]);
    }
    

    public function deleteUser()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $user_id = $_SESSION['user_id'];

        $userModel = new User();
        $admin = $userModel->find($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            exit();
        }

        if (isset($_GET['id'])) {
            $memberId = $_GET['id'];

            if ($userModel->deleteUser($memberId)) {
                $_SESSION['success_message'] = 'Utilisateur supprimé avec succès.';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la suppression de l\'utilisateur.';
            }
        }

        header('Location: /dashboard/admin/users');
        exit();
    }

    public function editUserProfile()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $currentUserId = $_SESSION['user_id'];
    $currentUser = $this->userModel->getUserById($currentUserId);
    if ($currentUser['status'] !== 'admin') {
        header('Location: /dashboard');
        exit;
    }

    if (!isset($_GET['id'])) {
        $_SESSION['error'] = 'Utilisateur non trouvé.';
        header('Location: /dashboard/admin/users');
        exit;
    }

    $userId = $_GET['id'];

    $user = $this->userModel->getUserById($userId);

    if (!$user) {
        $_SESSION['error'] = 'Utilisateur introuvable.';
        header('Location: /dashboard/admin/users');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $birthDate = trim($_POST['birth_date']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $status = trim($_POST['status']); 

        if (empty($firstName) || empty($lastName) || empty($email)) {
            $error = 'Les champs prénom, nom et email sont obligatoires.';
            echo $this->view->render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
            return;
        }

        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'birth_date' => $birthDate,
            'address' => $address,
            'phone' => $phone,
            'status' => $status, 
        ];

        $result = $this->userModel->updateUserProfile($userId, $data);

        if ($result) {
            $_SESSION['success_message'] = 'Le profil a été mis à jour avec succès.';
            header('Location: /dashboard/admin/users');
            exit;
        } else {
            $error = 'Une erreur est survenue lors de la mise à jour des informations.';
            echo $this->view->render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
        }
    } else {
        echo $this->view->render('dashboard/profile/index', ['user' => $user, 'ifAdminuser' => $currentUser]);
    }
}


}