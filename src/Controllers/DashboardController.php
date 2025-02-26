<?php

namespace Controllers;

use Core\Auth;
use Models\Stats;
use Core\View;
use Models\User;
use Models\Subscription;
use Core\Router;
use Core\RouteProvider;

class DashboardController implements RouteProvider
{
    public static function routes(): void
    {
        Router::get('/dashboard', self::class . '@showDashboard', Auth::requireLogin());
        Router::get('/dashboard/{category}/*', self::class . '@contentLoader', Auth::requireLogin());
        Router::get('/ajax/dashboard/{category}/*', self::class . '@ajaxContentLoader', Auth::requireLogin());
    }

    public function showDashboard()
    {
        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);

        $viewData = $this->getCommonViewData($user);

        echo View::render('layouts/dashboard', $viewData);
    }

    public function contentLoader($category, $wildcard = '')
    {
        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);
        $viewData = $this->getCommonViewData($user);

        try {
            $viewData = $this->handleCategoryLogic($category, $wildcard, $viewData);
            $viewPath = $this->getViewPath($category, $wildcard);
            
            $viewData['dataView'] = $viewPath;
            echo View::render('layouts/dashboard', $viewData);
            
        } catch (\Exception $e) {
            echo "<p>Content not found: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    public function ajaxContentLoader($category, $wildcard = '')
    {
        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);
        $viewData = $this->getCommonViewData($user);

	$hasActiveSubscription = Subscription::hasActiveSubscription($userId);
        $subscriptionInfo = $hasActiveSubscription ? Subscription::getStripeSubscriptionId($userId) : null;
        

        $is_subscribed = $subscriptionInfo["status"] ?? false;

        if (!$is_subscribed) {
            echo "<p> VOUS N'ÊTES PAS ABONNE</p>";
        }

	try {
            $viewData = $this->handleCategoryLogic($category, $wildcard, $viewData);
            $viewPath = $this->getViewPath($category, $wildcard);

            echo View::render($viewPath, $viewData);
           
        } catch (\Exception $e) {
            echo "<p>Content not found: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    protected function handleCategoryLogic($category, $wildcard, $viewData)
    {
        $segments = !empty($wildcard) ? explode('/', ltrim($wildcard, '/')) : [];

        if ($category === 'admin' && $segments[0] === 'users') {
            $searchTerm = $_GET['search'] ?? '';
            $users = !empty($searchTerm) ? User::searchUsers($searchTerm) : User::getAllUsers();
            $viewData = array_merge($viewData, [
                'users' => $users,
                'searchTerm' => $searchTerm,
                'dataView' => 'admin/users'
            ]);
        } elseif ($category === "events") {
            $viewData = array_merge($viewData, ['dataView' => 'events_dash']);
        } elseif ($category === "training") {
            $viewData = array_merge($viewData, ['dataView' => 'training']);
        } elseif ($category === 'profile') {
            $viewData = array_merge($viewData, ['dataView' => 'profile']);
        } elseif ($category === 'suivi') {
            $viewData = array_merge($viewData, ['dataView' => 'suivi']);
        } elseif ($category === 'booking') {
            $viewData = array_merge($viewData, ['dataView' => 'booking']);
        } elseif ($category === 'coaches') {
            $viewData = array_merge($viewData, ['dataView' => 'trainers']);
        } elseif ($category === 'stats') {
            $viewData = array_merge($viewData, ['dataView' => 'stats']);
        } elseif ($category === 'ranking') { 
            $viewData = array_merge($viewData, ['dataView' => 'ranking']);
        }
        return $viewData;
    }

    protected function getViewPath($category, $wildcard)
    {
        $segments = !empty($wildcard) ? explode('/', ltrim($wildcard, '/')) : [];
        $viewPath = 'dashboard';
        if ($category == "dashboard") {
            $viewPath .= '/index';
        } else if (!empty($segments)) {
            $viewPath .= '/' . $category . '/' . implode('/', $segments) . '/index';
        } else {
            $viewPath .= '/' . $category . '/index';
        }

        return $viewPath;
    }

    protected function getCommonViewData($user)
    {
        $userId = $user['member_id'];
        $hasActiveSubscription = Subscription::hasActiveSubscription($userId);
        $subscriptionInfo = $hasActiveSubscription ? Subscription::getStripeSubscriptionId($userId) : null;

        $viewData = [
            'user' => $user,
            'hasActiveSubscription' => $subscriptionInfo["status"] ?? false,
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
            $viewData = array_merge($viewData, [
                'totalUsers' => Stats::getTotalUsers(),
                'recentRegistrations' => Stats::getRecentRegistrations(),
                'activeSubscriptions' => Stats::getActiveSubscriptionsCount(),
                'globalOccupancyRate' => Stats::getGlobalOccupancyRate(),
                'topActivities' => Stats::getTop5Activities(),
                'memberStatusDistribution' => Stats::getMemberStatusDistribution(),
                'reservationsByDay' => Stats::getReservationsByDay(),
                'averageMemberAge' => Stats::getAverageMemberAge(),
                'retentionRate' => Stats::getMemberRetentionRate()
            ]);
        }

        return $viewData;
    }
}
