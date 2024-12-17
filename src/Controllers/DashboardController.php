<?php

namespace Controllers;

use Core\View;
use Models\Member;
use Models\User;
use Models\Subscription;

class DashboardController
{

    public function showDashboard()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); // pas connecté
            exit;
        }


        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);


        if ($user) {
            $memberId = $user['member_id']; // Récupération de member_id
        }
    
        $hasActiveSubscription = Subscription::hasActiveSubscription($userId);
        $subscriptionInfo = null;

        if ($hasActiveSubscription) {
            $subscriptionInfo = Subscription::getStripeSubscriptionId($userId);
        }

        $hasActiveSubscription = $subscriptionInfo["status"];


        echo View::render('dashboard/index', ['user' => $user, 'hasActiveSubscription' => $hasActiveSubscription, 'subscription' => [
            'plan_name' =>  $subscriptionInfo["subscription_type"] ?? "Aucun",
            'start_date' =>$subscriptionInfo["start_date"] ?? "Aucun",
            'end_date' => $subscriptionInfo["end_date"] ?? "Aucun",
            'amount' => $subscriptionInfo["amount"] ?? 0,
            'currency' => $subscriptionInfo["currency"] ?? '€',
            'status' => $subscriptionInfo["status"] ?? "Aucun"
        ]]);
    }

    public function showProfile() 
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);

    if (!$user) {
        header('Location: /error');
        exit;
    }

    echo View::render('dashboard/profile/index', ['user' => $user]);
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
                $user = User::getUserById($userId);
                echo View::render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
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

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $uploadDir = 'uploads/profile_pictures/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
                $targetFile = $uploadDir . $fileName;

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);

                if (!in_array($fileType, $allowedTypes)) {
                    $error = 'Type de fichier non autorisé. Seuls JPEG, PNG et GIF sont acceptés.';
                    $user = User::getUserById($userId);
                    echo View::render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
                    return;
                }

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                    if (!empty($_POST['current_profile_picture']) && file_exists($_POST['current_profile_picture'])) {
                        unlink($_POST['current_profile_picture']);
                    }
                    $data['profile_picture'] = $targetFile;
                } else {
                    $error = 'Erreur lors du téléchargement de la photo de profil.';
                    $user = User::getUserById($userId);
                    echo View::render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
                    return;
                }
            }

            if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
                if (!User::verifyCurrentPassword($userId, $_POST['current_password'])) {
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

            if (User::updateUserProfile($userId, $data)) {
                $_SESSION['success_message'] = 'Profil mis à jour avec succès.';
                header('Location: /dashboard/profile');
                exit();
            } else {
                $error = 'Échec de la mise à jour du profil.';
                $user = User::getUserById($userId);
                echo View::render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
            }
        } else {
            header('Location: /dashboard/profile');
            exit;
        }

    }


    public function manageUsers() {

        $user_id = $_SESSION['user_id'];


        $membre = User::getUserById($user_id);

        if ($membre['status'] !== 'admin') {
            header("Location: /dashboard");
            exit;
        }
    
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        if (!empty($searchTerm)) {
            $users = User::searchUsers($searchTerm);
        } else {
            $users = User::getAllUsers();
        }
        echo View::render('dashboard/admin/users/index', ['users' => $users, 'membre' => $membre]);
    }
    

    public function deleteUser()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            exit();
        }

        if (isset($_GET['id'])) {
            $memberId = $_GET['id'];

            if (User::deleteUser($memberId)) {
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
        $currentUser = User::getUserById($currentUserId);
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

        $user = User::getUserById($userId);

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
                echo View::render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
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

            // Gestion de la photo de profil pour l'administrateur
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $uploadDir = 'uploads/profile_pictures/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
                $targetFile = $uploadDir . $fileName;

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);

                if (!in_array($fileType, $allowedTypes)) {
                    $error = 'Type de fichier non autorisé. Seuls JPEG, PNG et GIF sont acceptés.';
                    echo View::render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
                    return;
                }

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                    if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                        unlink($user['profile_picture']);
                    }
                    $data['profile_picture'] = $targetFile;
                } else {
                    $error = 'Erreur lors du téléchargement de la photo de profil.';
                    echo View::render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
                    return;
                }
            }

            $result = User::updateUserProfile($userId, $data);

            if ($result) {
                $_SESSION['success_message'] = 'Le profil a été mis à jour avec succès.';
                header('Location: /dashboard/admin/users');
                exit;
            } else {
                $error = 'Une erreur est survenue lors de la mise à jour des informations.';
                echo View::render('dashboard/profile/index', ['error' => $error, 'user' => $user]);
            }
        } else {
            echo View::render('dashboard/profile/index', ['user' => $user, 'ifAdminuser' => $currentUser]);
        }
    }

    public function loadContent()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);

        if (!$user) {
            header('Location: /error');
            exit;
        }

        if ($user['status'] === 'admin' || $user['status'] === 'coach') {
            echo View::render('dashboard/admin', ['user' => $user]);
        } else {
            echo View::render('dashboard/member', ['user' => $user]);
        }
    }

}