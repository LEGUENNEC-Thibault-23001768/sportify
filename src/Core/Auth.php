<?php


namespace Core;


use Models\User;
use Models\Subscription;

final class Auth
{
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin()
    {
        return function() {
            if (!Auth::isLoggedIn()) {
                header('Location: /login');
                return false;
            }
            return true;
        };
    }

    public static function isAdmin()
    {
        return function() {
            if (!Auth::isLoggedIn()) {
                header('Location: /login');
                return false;
            }

            $user = User::getUserById($_SESSION['user_id']);
            if (!$user || $user['status'] !== 'admin') {
                header('Location: /dashboard');
                return false;
            }
            return true;
        };
    }

    public static function isSubscribed()
    {
        return function() {
            if (!self::isLoggedIn()) {
                header('Location: /login');
                return false;
            }

            $userId = $_SESSION['user_id'];
            if (!Subscription::hasActiveSubscription($userId)) {
                header('Location: /404');
                return false;
            }

            return true;
        };
    }

    public static function isCoach()
    {
        return function() {
            if (!self::isLoggedIn()) {
                header('Location: /login');
                return false;
            }

            $user = User::getUserById($_SESSION['user_id']);
            if (!$user || $user['status'] !== 'coach') {
                header('Location: /not-a-coach');
                return false;
            }

            return true;
        };
    }


}