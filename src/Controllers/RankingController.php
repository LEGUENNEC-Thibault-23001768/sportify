<?php

namespace Controllers;

use Core\Auth;
use Core\Config;
use Core\View;
use Models\Stats;
use Models\User;
use Core\Router;
use Core\RouteProvider;
use Core\APIResponse;

class RankingController implements RouteProvider
{

    public static function routes(): void
    {
       Router::get('/api/ranking', self::class . '@getRankingData', Auth::requireLogin()); 
      
    }

    public function getRankingData() {
        $sortBy = $_GET['sort_by'] ?? 'total_play_time_seconds';
        $sortOrder = $_GET['sort_order'] ?? 'desc';
        $sport = $_GET['sport'] ?? 'all';
    
        $usersBySport = Stats::getUsersRankedBySport($sortBy, $sortOrder, $sport);
    
        $response = new APIResponse();
        return $response->setStatusCode(200)->setData(['users' => $usersBySport])->send();
    }

}