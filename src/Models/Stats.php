<?php
namespace Models;
use Core\Database;
use PDO;

class Stats
{
    /**
     * @param $userId
     * @return array
     */
     public static function getUserPerformances($userId): array
    {
        $sql = "SELECT * FROM PERFORMANCE WHERE member_id = :member_id ORDER BY performance_date DESC";
        $params = [':member_id' => $userId];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserAggregatedPerformances(int $userId): array
    {
        $sql = "SELECT 
            activity,
            SUM(CASE WHEN activity = 'RPM' THEN play_time ELSE TIME_TO_SEC(play_time) END) AS total_time,
            SUM(CASE WHEN activity = 'RPM' THEN calories ELSE 0 END) as total_calories,
            SUM(CASE WHEN activity = 'RPM' THEN distance ELSE 0 END) as total_distance,
            SUM(CASE WHEN activity != 'RPM' THEN score ELSE 0 END) as total_score
             
        FROM PERFORMANCE
        WHERE member_id = :member_id
        GROUP BY activity";

    $params = [
        ':member_id' => $userId,
    ];

        $result = Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
        return self::formatTimeDataForAggregated($result);
    }
     
    public static function getAllUsersAggregatedPerformances(): array
    {
        $sql = "SELECT 
            AVG(play_time) AS avg_time,
            AVG(calories) AS avg_calories,
            AVG(distance) AS avg_distance
            FROM PERFORMANCE
            WHERE activity = 'RPM'";
        
        $result = Database::query($sql)->fetch(PDO::FETCH_ASSOC);
    
        return [
            'avg_time' => isset($result['avg_time']) ? round($result['avg_time'], 2) : 0,
            'avg_calories' => isset($result['avg_calories']) ? round($result['avg_calories'], 2) : 0,
            'avg_distance' => isset($result['avg_distance']) ? round($result['avg_distance'], 2) : 0,
        ];
    }
    
    /**
     * @return mixed
     */
    public static function getTotalUsers(): mixed
    {
        $sql = "SELECT COUNT(*) AS total FROM MEMBER";
        return Database::query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * @return array
     */
    public static function getRecentRegistrations(): array
    {
        $sql = "SELECT COUNT(*) AS registrations, WEEK(creation_date) AS week_number
                FROM MEMBER
                WHERE creation_date >= DATE(NOW()) - INTERVAL 4 WEEK
                GROUP BY WEEK(creation_date)";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $userId
     * @return array
     */
    public static function getUserTopActivities($userId): array
    {
        $sql = "SELECT activity_type, COUNT(*) AS total_reservations
                FROM COURT_RESERVATION
                JOIN COURT ON COURT_RESERVATION.court_id = COURT.court_id
                WHERE member_id = :member_id
                GROUP BY activity_type
                ORDER BY total_reservations DESC
                LIMIT 3";
        $params = [':member_id' => $userId];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $userId
     * @return array
     */
    public static function getPerformanceDataRPM($userId): array
    {
         $sql = "SELECT 
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 2 THEN TIME_TO_SEC(play_time) END) AS monday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 3 THEN TIME_TO_SEC(play_time) END) AS tuesday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 4 THEN TIME_TO_SEC(play_time) END) AS wednesday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 5 THEN TIME_TO_SEC(play_time) END) AS thursday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 6 THEN TIME_TO_SEC(play_time) END) AS friday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 7 THEN TIME_TO_SEC(play_time) END) AS saturday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 1 THEN TIME_TO_SEC(play_time) END) AS sunday
            FROM PERFORMANCE 
            WHERE member_id = :member_id AND activity = 'RPM'";
        
           
         $params = [
            ':member_id' => $userId
         ];
       $result = Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
         return self::formatTimeData($result);
    }


    /**
     * @param $userId
     * @param $activity
     * @return array
     */
    public static function getPerformanceData($userId, $activity): array
    {
         $sql = "SELECT 
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 2 THEN TIME_TO_SEC(play_time) END) AS monday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 3 THEN TIME_TO_SEC(play_time) END) AS tuesday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 4 THEN TIME_TO_SEC(play_time) END) AS wednesday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 5 THEN TIME_TO_SEC(play_time) END) AS thursday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 6 THEN TIME_TO_SEC(play_time) END) AS friday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 7 THEN TIME_TO_SEC(play_time) END) AS saturday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 1 THEN TIME_TO_SEC(play_time) END) AS sunday
            FROM PERFORMANCE 
            WHERE member_id = :member_id AND activity = :activity";

          $params = [
              ':member_id' => $userId,
              ':activity' => $activity
          ];
        $result = Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
       return self::formatTimeData($result);
    }

     /**
     * @param $userId
     * @param $activity
     * @return array
     */
    public static function getPerformanceDataCouche($userId, $activity): array
    {
         $sql = "SELECT 
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 2 THEN TIME_TO_SEC(play_time) END) AS monday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 3 THEN TIME_TO_SEC(play_time) END) AS tuesday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 4 THEN TIME_TO_SEC(play_time) END) AS wednesday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 5 THEN TIME_TO_SEC(play_time) END) AS thursday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 6 THEN TIME_TO_SEC(play_time) END) AS friday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 7 THEN TIME_TO_SEC(play_time) END) AS saturday,
            SUM(CASE WHEN DAYOFWEEK(performance_date) = 1 THEN TIME_TO_SEC(play_time) END) AS sunday
            FROM PERFORMANCE 
            WHERE member_id = :member_id AND activity = :activity";
           
        $params = [
            ':member_id' => $userId,
            ':activity' => $activity
         ];
         $result = Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return self::formatTimeData($result);
    }
    /**
     * @return mixed
     */
    public static function getActiveSubscriptionsCount(): mixed
    {
        $sql = "SELECT COUNT(*) AS active_subscriptions FROM SUBSCRIPTION WHERE status = 'active'";
        return Database::query($sql)->fetch(PDO::FETCH_ASSOC)['active_subscriptions'];
    }

    /**
     * @return mixed
     */
    public static function getGlobalOccupancyRate(): mixed
    {
        $sql = "SELECT AVG(occupation_rate) AS average_occupation_rate
                FROM (
                    SELECT COUNT(*) AS occupation_rate, DATE(reservation_date) AS reservation_day
                    FROM COURT_RESERVATION
                    WHERE reservation_date >= CURDATE() - INTERVAL 7 DAY
                    GROUP BY reservation_day, court_id
                ) AS daily_occupation";
        return Database::query($sql)->fetch(PDO::FETCH_ASSOC)['average_occupation_rate'];
    }

    /**
     * @return array
     */
    public static function getTop5Activities(): array
    {
        $sql = "SELECT c.activity_type, COUNT(*) AS total_reservations
                FROM COURT_RESERVATION cr
                JOIN COURT c ON cr.court_id = c.court_id
                WHERE cr.reservation_date >= CURDATE() - INTERVAL 7 DAY
                GROUP BY c.activity_type
                ORDER BY total_reservations DESC
                LIMIT 5";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * @return array
     */
    public static function getMemberStatusDistribution(): array
    {
        $sql = "SELECT status, COUNT(*) AS count FROM MEMBER GROUP BY status";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nouvelle méthode pour obtenir le nombre de réservations par jour de la semaine (7 derniers jours)

    /**
     * @return array
     */
    public static function getReservationsByDay(): array
    {
        $sql = "SELECT DAYNAME(reservation_date) AS day_of_week, COUNT(*) AS total_reservations
                FROM COURT_RESERVATION
                WHERE reservation_date >= CURDATE() - INTERVAL 7 DAY
                GROUP BY day_of_week
                ORDER BY DAYOFWEEK(reservation_date)";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    //  obtenir l'âge moyen des membres

    /**
     * @return mixed
     */
    public static function getAverageMemberAge(): mixed
    {
        $sql = "SELECT AVG(TIMESTAMPDIFF(YEAR, birth_date, CURDATE())) AS average_age FROM MEMBER";
        return Database::query($sql)->fetch(PDO::FETCH_ASSOC)['average_age'];
    }

    //  obtenir le taux de rétention des membres (exemple sur les 6 derniers mois)

    /**
     * @return float|int
     */
    public static function getMemberRetentionRate(): float|int
    {
        $sql = "SELECT 
                    SUM(CASE WHEN end_date > NOW() THEN 1 ELSE 0 END) AS renewed,
                    COUNT(*) AS total
                FROM SUBSCRIPTION
                WHERE start_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status = 'active'";

        $result = Database::query($sql)->fetch(PDO::FETCH_ASSOC);

        if ($result['total'] > 0) {
            return ($result['renewed'] / $result['total']) * 100;
        } else {
            return 0;
        }
    }

    public static function saveRpmPerformance(int $userId, ?string $playTime, ?int $calories = null, ?float $distance = null): bool
    {
        $sql = "INSERT INTO PERFORMANCE (member_id, activity, rpm, calories, distance, play_time, performance_date) 
                VALUES (:member_id, 'RPM', 1, :calories, :distance, :play_time, NOW())";
        $params = [
            ':member_id' => $userId,
            ':play_time' => $playTime,
            ':calories' => $calories,
            ':distance' => $distance,
        ];

        try {
            Database::query($sql, $params);
            return true;
        } catch (\PDOException $e) {
            error_log("Erreur lors de l'enregistrement des performances RPM : " . $e->getMessage());
            return false;
        }
    }

    public static function saveOtherPerformance(int $userId, string $sport, array $stats): bool
    {
         $sql = "INSERT INTO PERFORMANCE (member_id, activity, score, play_time, calories, distance, performance_date) 
                VALUES (:member_id, :activity, :score, :play_time, :calories, :distance, NOW())";
           $params = [
                ':member_id' => $userId,
                ':activity' => $sport,
                ':score' => $stats[1] ?? null,
                ':play_time' => $stats[0] ?? null,
                ':calories' => $stats[2] ?? null,
                ':distance' => $stats[1] ?? null,
            ];
        try {
            Database::query($sql, $params);
             return true;
        } catch (\PDOException $e) {
             error_log("Erreur lors de l'enregistrement des performances: " . $e->getMessage());
            return false;
        }
    }


    private static function formatTimeData(array $data): array
    {
        $formattedData = [];
        foreach ($data as $key => $seconds) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $formattedData[$key] = $hours + ($minutes / 60);
        }
        return $formattedData;
    }
       private static function formatTimeDataForAggregated(array $data): array
    {
        $formattedData = [];
        foreach ($data as $item) {
          if (isset($item['avg_time'])) {
                $seconds = $item['avg_time'];
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
              $item['avg_time'] =  $hours + ($minutes / 60);
         }
            $formattedData[] = $item;
        }
        return $formattedData;
    }

    public static function getUsersRankedBySport()
    {
        $sql = "
            SELECT
                m.member_id,
                m.first_name,
                m.last_name,
                COALESCE(SUM(CASE WHEN p.activity = :rpmActivity THEN TIME_TO_SEC(p.play_time) ELSE 0 END), 0) as total_rpm_time,
                COALESCE(SUM(CASE WHEN p.activity = :musculationActivity THEN TIME_TO_SEC(p.play_time) ELSE 0 END), 0) as total_musculation_time,
                COALESCE(SUM(CASE WHEN p.activity = :boxeActivity THEN TIME_TO_SEC(p.play_time) ELSE 0 END), 0) as total_boxe_time,
                COALESCE(SUM(CASE WHEN p.activity = :footballActivity THEN TIME_TO_SEC(p.play_time) ELSE 0 END), 0) as total_football_time,
                COALESCE(SUM(CASE WHEN p.activity = :tennisActivity THEN TIME_TO_SEC(p.play_time) ELSE 0 END), 0) as total_tennis_time,
                COALESCE(SUM(CASE WHEN p.activity = :basketballActivity THEN TIME_TO_SEC(p.play_time) ELSE 0 END), 0) as total_basketball_time
            FROM MEMBER m
            LEFT JOIN PERFORMANCE p ON m.member_id = p.member_id
            GROUP BY m.member_id, m.first_name, m.last_name
            ORDER BY 
                total_rpm_time DESC,
                total_musculation_time DESC,
                total_boxe_time DESC,
                total_football_time DESC,
                total_tennis_time DESC,
                total_basketball_time DESC
        ";

        $params = [
            ':rpmActivity' => 'rpm',
            ':musculationActivity' => 'musculation',
            ':boxeActivity' => 'boxe',
            ':footballActivity' => 'football',
            ':tennisActivity' => 'tennis',
            ':basketballActivity' => 'basketball',
        ];

        try {
            return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("PDOException in getUsersRankedBySport: " . $e->getMessage());
            return [];
        }
    }
}
