<?php

namespace Controllers;

use Core\Auth;
use Core\Config;
use Core\View;
use Models\Stats;
use Models\User;
use Core\Router;
use Core\RouteProvider;

class RankingController implements RouteProvider
{

    public static function routes(): void
    {
        Router::get('/dashboard/ranking', self::class . '@showRanking', Auth::requireLogin());
    }

    public function showRanking()
    {
        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);

        $usersBySport = Stats::getUsersRankedBySport();

        $viewData = [
            'users' => $usersBySport,
            'title' => 'Classement des utilisateurs par activité',
            'user' => $user
        ];
        
        echo View::renderWithLayout('dashboard/ranking/index', 'layouts/dashboard', $viewData);
    }
}