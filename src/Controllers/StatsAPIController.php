<?php

namespace Controllers;
use Core\APIController;
use Core\APIResponse;
use Models\Stats;
use Models\User;
use Core\Router;
use Core\RouteProvider;
use Core\Auth;

class StatsAPIController extends APIController implements RouteProvider
{
    public static function routes() : void
    {
        Router::apiResource('/api/stats', self::class, Auth::requireLogin());
    }

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

        if ($_SESSION['user_status'] === 'admin') {
            $totalUsers = Stats::getTotalUsers();
            $recentRegistrations = Stats::getRecentRegistrations();
            $activeSubscriptions = Stats::getActiveSubscriptionsCount();
            $globalOccupancyRate = Stats::getGlobalOccupancyRate();
            $topActivities = Stats::getTop5Activities();
            $memberStatusDistribution = Stats::getMemberStatusDistribution();
            $reservationsByDay = Stats::getReservationsByDay();
            $averageMemberAge = Stats::getAverageMemberAge();
            $retentionRate = Stats::getMemberRetentionRate();
            $averageRpmStats = Stats::getAllUsersAggregatedPerformances();


            $adminStats = [
               'totalUsers' => $totalUsers,
                'recentRegistrations' => $recentRegistrations,
                'activeSubscriptions' => $activeSubscriptions,
                'globalOccupancyRate' => $globalOccupancyRate,
                'topActivities' => $topActivities,
               'memberStatusDistribution' => $memberStatusDistribution,
                'reservationsByDay' => $reservationsByDay,
               'averageMemberAge' => $averageMemberAge,
               'averageRpmStats' => $averageRpmStats,
                'retentionRate' => $retentionRate
             ];

             return $response->setStatusCode(200)->setData($adminStats)->send();

        } else {
        $aggregatedPerformances = Stats::getUserAggregatedPerformances($currentUserId);
        $performanceDataRpm = Stats::getPerformanceDataRPM($currentUserId);
        $averageRpmStats = Stats::getAllUsersAggregatedPerformances(); 

        $userStats = [
            'aggregatedStats' => $aggregatedPerformances,
            'performanceDataRpm' => $performanceDataRpm,
            'averageRpmStats' => $averageRpmStats, 
        ];
            return $response->setStatusCode(200)->setData($userStats)->send();
        }
    }

   /**
     * Handles POST requests to save RPM performance data.
     *
     * @return
     */
    public function post()
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];

        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }

       if (isset($_POST['rpm'])) {
           
            if (!isset($_POST['playTime']) || !is_numeric($_POST['playTime'])) {
                return $response->setStatusCode(400)->setData(['error' => 'Invalid RPM data'])->send();
            }
            $calories = $_POST['calories'] ?? null;
            $distance = $_POST['distance'] ?? null;
             $playTime = $_POST['playTime'] ?? null;


            if ($distance !== null) {
                $distance = (float)$distance;
            }
             $success = Stats::saveRpmPerformance(
                $currentUserId,
               $playTime,
                $calories,
                $distance
            );

            if ($success) {
                return $response->setStatusCode(201)->setData(['message' => 'RPM performance data saved successfully'])->send();
            } else {
                return $response->setStatusCode(500)->setData(['error' => 'Failed to save RPM performance data'])->send();
            }
        } else {
          
          $sport = $_POST['sport'];
          $stats = $_POST['stats'];
            
           $success = Stats::saveOtherPerformance(
                $currentUserId,
                $sport,
              [
                    $stats[0]  ?? null,
                    $stats[1]  ?? null,
                     $stats[2]  ?? null,
                    $stats[3]  ?? null,
                ]
            );
           if ($success) {
              return $response->setStatusCode(201)->setData(['message' => 'Stats saved successfully'])->send();
            } else {
                return $response->setStatusCode(500)->setData(['error' => 'Failed to save  performance data'])->send();
            }
        }
    }
}