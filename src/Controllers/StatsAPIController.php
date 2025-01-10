<?php

namespace Controllers;

use Core\APIController;
use Core\APIResponse;
use Models\Stats;
use Models\User;

class StatsController extends APIController
{
    /**
     * Handles GET requests for stats data.
     * @param $userId
     * @return null
     */
    public function get($userId = null)
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];

        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }

        $user = User::getUserById($currentUserId);

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

            $adminStats = [
              'user' => $user,
               'totalUsers' => $totalUsers,
                'recentRegistrations' => $recentRegistrations,
                'activeSubscriptions' => $activeSubscriptions,
                'globalOccupancyRate' => $globalOccupancyRate,
                'topActivities' => $topActivities,
               'memberStatusDistribution' => $memberStatusDistribution,
                'reservationsByDay' => $reservationsByDay,
               'averageMemberAge' => $averageMemberAge,
                'retentionRate' => $retentionRate
             ];

             return $response->setStatusCode(200)->setData($adminStats)->send();

        } else {
            $performances = Stats::getUserPerformances($currentUserId);
            $topActivities = Stats::getUserTopActivities($currentUserId);
            $performanceDataFootball = Stats::getPerformanceData($currentUserId, 'Football');
            $performanceDataBasketball = Stats::getPerformanceData($currentUserId, 'Basketball');
            $performanceDataMusculation = Stats::getPerformanceDataCouche($currentUserId, 'Développé couché');
            $userStats = [
                'user' => $user,
                'stats' => $performances,
                'topActivities' => $topActivities,
               'performanceDataFootball' => $performanceDataFootball,
                'performanceDataBasketball' => $performanceDataBasketball,
                'performanceDataMusculation' => $performanceDataMusculation
           ];
            return $response->setStatusCode(200)->setData($userStats)->send();
        }
    }
}