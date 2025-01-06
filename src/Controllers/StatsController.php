<?php

namespace Controllers;

use Core\View;
use Models\Stats;
use Models\User;

class StatsController
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = User::getUserById($userId);
        $performances = Stats::getUserPerformances($userId);

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
            
            echo View::render('dashboard/stats/index', [
                'user' => $user,
                'stats' => $performances,
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
        } else {
            $topActivities = Stats::getUserTopActivities($userId);
            $performanceDataFootball = Stats::getPerformanceData($userId, 'Football');
            $performanceDataBasketball = Stats::getPerformanceData($userId, 'Basketball');
            $performanceDataMusculation = Stats::getPerformanceDataCouche($userId, 'Développé couché');

            echo View::render('dashboard/stats/index', [
                'stats' => $performances,
                'topActivities' => $topActivities,
                'performanceDataFootball' => $performanceDataFootball,
                'performanceDataBasketball' => $performanceDataBasketball,
                'performanceDataMusculation' => $performanceDataMusculation
            ]);
        }
    }
}