<?php

namespace Controllers;

use Core\APIResponse;
use Core\Auth;
use Core\View;
use Exception;
use Models\Stats;
use Models\Subscription;
use Models\User;

class DashboardController
{
    /**
     * @return void
     * @throws Exception
     */
    public function showDashboard(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); // pas connecté
            return;
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

        $viewData = [
            'user' => $user,
            'hasActiveSubscription' => $hasActiveSubscription,
            'subscription' => [
                'plan_name' => $subscriptionInfo["subscription_type"] ?? "Aucun",
                'start_date' => $subscriptionInfo["start_date"] ?? "Aucun",
                'end_date' => $subscriptionInfo["end_date"] ?? "Aucun",
                'amount' => $subscriptionInfo["amount"] ?? 0,
                'currency' => $subscriptionInfo["currency"] ?? '€',
                'status' => $subscriptionInfo["status"] ?? "Aucun"
            ]
        ];

        if ($user['status'] === 'admin') {
            $totalUsers = Stats::getTotalUsers();
            $recentRegistrations = Stats::getRecentRegistrations();
            $activeSubscriptions = Stats::getActiveSubscriptionsCount();
            $globalOccupancyRate = Stats::getGlobalOccupancyRate();
            $topActivities = Stats::getTop5Activities();
            $memberStatusDistribution = Stats::getMemberStatusDistribution();
            $reservationsByDay = Stats::getReservationsByDay();
            $averageMemberAge = Stats::getAverageMemberAge();
            $retentionRate = Stats::getMemberRetentionRate();

            $viewData = array_merge($viewData, [
                'totalUsers' => $totalUsers,
                'recentRegistrations' => $recentRegistrations,
                'activeSubscriptions' => $activeSubscriptions,
                'globalOccupancyRate' => $globalOccupancyRate,
                'topActivities' => $topActivities,
                'memberStatusDistribution' => $memberStatusDistribution,
                'reservationsByDay' => $reservationsByDay,
                'averageMemberAge' => $averageMemberAge,
                'retentionRate' => $retentionRate
            ]);
        }

        echo View::render('dashboard/index', $viewData);
    }

    /**
     * @return void
     */
    public function showProfile(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }

        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);
        $response = new APIResponse();

        if (!$user) {
            $response->setStatusCode(404)->setData(['error' => 'User not found'])->send();
            return;
        }

        $response->setStatusCode(200)->setData($user)->send();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function manageUsers(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }

        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        $users = !empty($searchTerm) ? User::searchUsers($searchTerm) : User::getAllUsers();
        echo View::render('dashboard/admin/users/index', ['users' => $users, 'searchTerm' => $searchTerm]);
    }

    // User Management (Admin)

    /**
     * @param $userId
     * @return void
     */
    public function deleteUser($userId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }
        $response = new APIResponse();

        if (User::deleteUser($userId)) {
            $response->setStatusCode(200)->setData(['message' => 'Utilisateur supprimé avec succès.'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Erreur lors de la suppression de l\'utilisateur.'])->send();
        }
    }

    /**
     * @param $userId
     * @return void
     */
    public function getUserApi($userId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }
        $response = new APIResponse();

        $user = User::getUserById($userId);

        if ($user) {
            $response->setStatusCode(200)->setData($user)->send();
        } else {
            $response->setStatusCode(404)->setData(['error' => 'User not found'])->send();
        }
    }

    // API Endpoint for Editing Users (Admin)

    /**
     * @param $userId
     * @return void
     * @throws Exception
     */
    public function updateUserApi($userId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }
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

    /**
     * @return void
     * @throws Exception
     */
    public function updateUserProfile(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }
        $response = new APIResponse();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        } else {
            $response->setStatusCode(400)->setData(['error' => 'Invalid request method.'])->send();
        }
    }

    /**
     * @param $userId
     * @return void
     */
    public function getUserSubscription($userId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }
        $response = new APIResponse();

        $subscription = Subscription::getStripeSubscriptionId($userId);

        if ($subscription) {
            $response->setStatusCode(200)->setData($subscription)->send();
        } else {
            $response->setStatusCode(404)->setData(['error' => 'Subscription not found'])->send();
        }
    }

    /**
     * @param $userId
     * @return void
     */
    public function updateUserSubscription($userId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }
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


    /**
     * @return void
     * @throws Exception
     */
    public function loadContent(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);

        if (!$user) {
            header('Location: /error');
            return;
        }

        if ($user['status'] === 'admin' || $user['status'] === 'coach') {
            echo View::render('dashboard/admin', ['user' => $user]);
        } else {
            echo View::render('dashboard/member', ['user' => $user]);
        }
    }

    /**
     * @param $userId
     * @return void
     */
    public function cancelUserSubscription($userId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }
        $response = new APIResponse();

        if (Subscription::cancelSubscription($userId)) {
            $response->setStatusCode(200)->setData(['message' => 'Subscription cancelled successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to cancel subscription'])->send();
        }
    }

    /**
     * @param $userId
     * @return void
     */
    public function resumeUserSubscription($userId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        $admin = User::getUserById($user_id);

        if ($admin['status'] !== 'admin') {
            header('Location: /dashboard');
            return;
        }
        $response = new APIResponse();

        if (Subscription::resumeSubscription($userId)) {
            $response->setStatusCode(200)->setData(['message' => 'Subscription resumed successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to resume subscription'])->send();
        }
    }

}