<?php

namespace Controllers;

use Core\Auth;
use Core\View;
use Core\APIResponse;
use Models\User;
use Models\Subscription;

class DashboardController
{
    public function showDashboard()
    {
        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);

        echo View::render('layouts/dashboard', ['user' => $user]);
    }

    public function contentLoader($category, $wildcard = '')
    {

        $wildcard = ltrim($wildcard, '/');
    
        $segments = !empty($wildcard) ? explode('/', $wildcard) : [];

        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);

        try {
            if ($category === 'admin' && $segments[0] === 'users') {
                $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
                $users = !empty($searchTerm) ? User::searchUsers($searchTerm) : User::getAllUsers();
                echo View::render('dashboard/admin/users/index', ['users' => $users, 'searchTerm' => $searchTerm, 'user' => $user, 'dataView' => 'admin/users']);
            } else if ($category === "events") {
                error_log(print_r("qsq",true));
                //echo View::render('dashboard/events/index', ['member' => $user]);  
                echo View::render('dashboard/events/index', ['user' => $user, 'dataView' => 'events']);
            } else {
                // Construct the view path using both category and segments
                $viewPath = 'dashboard/' . $category . '/' . implode('/', $segments) . '/index';
                try {
                    echo View::render($viewPath, ['user' => $user]);
                } catch (\Exception $e) {
                    // Handle cases where the view file doesn't exist
                    echo "<p>Content not found: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            } 
        } catch (\Exception $e) {
            //error_log(print_r($e,true));
            echo "<p>Content not found.</p>";
        }
    }

    public function showProfile()
    {

        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);
        $response = new APIResponse();

        if (!$user) {
            $response->setStatusCode(404)->setData(['error' => 'User not found'])->send();
            return;
        }

        $response->setStatusCode(200)->setData($user)->send();
    }

    public function updateUserProfile()
    {
        $response = new APIResponse();
        $userId = $_SESSION['user_id'];

        $data = json_decode(file_get_contents('php://input'), true);

        // Validate and sanitize input data
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $birthDate = trim($data['birth_date'] ?? '');
        $address = trim($data['address'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($email)) {
            $response->setStatusCode(400)->setData(['error' => 'Les champs prénom, nom et email sont obligatoires'])->send();
            return;
        }

        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'birth_date' => $birthDate,
            'address' => $address,
            'phone' => $phone,
        ];

        // Handle profile picture update if necessary

        if (User::updateUserProfile($userId, $updateData)) {
            $response->setStatusCode(200)->setData(['message' => 'Profil mis à jour avec succès.'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Échec de la mise à jour du profil.'])->send();
        }
    }

    // User Management (Admin)
    public function manageUsers()
    {
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        $users = !empty($searchTerm) ? User::searchUsers($searchTerm) : User::getAllUsers();
        echo View::render('dashboard/admin/users/index', ['users' => $users, 'searchTerm' => $searchTerm]);
    }

    public function deleteUser($userId)
    {
        
        $response = new APIResponse();
    
        if (User::deleteUser($userId)) {
            $response->setStatusCode(200)->setData(['message' => 'Utilisateur supprimé avec succès.'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Erreur lors de la suppression de l\'utilisateur.'])->send();
        }
    }

    // API Endpoint for Editing Users (Admin)
    public function getUserApi($userId)
    {
        $response = new APIResponse();

        $user = User::getUserById($userId);

        if ($user) {
            $response->setStatusCode(200)->setData($user)->send();
        } else {
            $response->setStatusCode(404)->setData(['error' => 'User not found'])->send();
        }
    }

    public function updateUserApi($userId)
    {
        
        $response = new APIResponse();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response->setStatusCode(400)->setData(['error' => 'Invalid request method'])->send();
            return;
        }

        $user = User::getUserById($userId);
        if (!$user) {
            $response->setStatusCode(404)->setData(['error' => 'User not found'])->send();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validate and sanitize input data
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $birthDate = trim($data['birth_date'] ?? '');
        $address = trim($data['address'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $status = trim($data['status'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($email)) {
            $response->setStatusCode(400)->setData(['error' => 'Les champs prénom, nom et email sont obligatoires'])->send();
            return;
        }

        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'birth_date' => $birthDate,
            'address' => $address,
            'phone' => $phone,
            'status' => $status,
        ];

        // Handle profile picture update if necessary

        if (User::updateUserProfile($userId, $updateData)) {
            $response->setStatusCode(200)->setData(['message' => 'User updated successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to update user'])->send();
        }
    }
    public function getUserSubscription($userId)
    {
        $response = new APIResponse();

        $subscription = Subscription::getStripeSubscriptionId($userId);

        if ($subscription) {
            $response->setStatusCode(200)->setData($subscription)->send();
        } else {
            $response->setStatusCode(404)->setData(['error' => 'Subscription not found'])->send();
        }
    }

    public function updateUserSubscription($userId)
    {
        $response = new APIResponse();
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response->setStatusCode(400)->setData(['error' => 'Invalid request method'])->send();
            return;
        }
    
        $data = json_decode(file_get_contents('php://input'), true);
    
        $subscriptionType = trim($data['subscription_type'] ?? '');
        $startDate = trim($data['start_date'] ?? '');
        $endDate = trim($data['end_date'] ?? '');
        $amount = trim($data['amount'] ?? '');
    
        if (empty($subscriptionType) || empty($startDate) || empty($endDate) || empty($amount)) {
            $response->setStatusCode(400)->setData(['error' => 'Missing required fields'])->send();
            return;
        }
    
        if (Subscription::updateSubscriptionDetails($userId, $subscriptionType, $startDate, $endDate, $amount)) {
            $response->setStatusCode(200)->setData(['message' => 'Subscription updated successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to update subscription'])->send();
        }
    }
    

    public function cancelUserSubscription($userId)
    {
        $response = new APIResponse();

        if (Subscription::cancelSubscription($userId)) {
            $response->setStatusCode(200)->setData(['message' => 'Subscription cancelled successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to cancel subscription'])->send();
        }
    }

    public function resumeUserSubscription($userId)
    {
        $response = new APIResponse();

        if (Subscription::resumeSubscription($userId)) {
            $response->setStatusCode(200)->setData(['message' => 'Subscription resumed successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to resume subscription'])->send();
        }
    }
}