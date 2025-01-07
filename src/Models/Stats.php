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
     * @param $activity
     * @return array
     */
    public static function getPerformanceData($userId, $activity): array
    {
        $sql = "SELECT performance_date, score, play_time 
                FROM PERFORMANCE 
                WHERE member_id = :member_id AND activity = :activity 
                ORDER BY performance_date ASC";
        $params = [
            ':member_id' => $userId,
            ':activity' => $activity
        ];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $userId
     * @param $activity
     * @return array
     */
    public static function getPerformanceDataCouche($userId, $activity): array
    {
        $sql = "SELECT performance_date, score, play_time 
                FROM PERFORMANCE 
                WHERE member_id = :member_id AND activity = :activity 
                ORDER BY performance_date ASC";
        $params = [
            ':member_id' => $userId,
            ':activity' => $activity
        ];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
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
}