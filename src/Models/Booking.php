<?php

namespace Models;

use Core\Database;
use PDO;

class Booking
{

    /**
     * @param $court_id
     * @param $reservation_date
     * @param $start_time
     * @param $end_time
     * @return bool
     */
    public static function isAvailable($court_id, $reservation_date, $start_time, $end_time): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM COURT_RESERVATION 
            WHERE court_id = :court_id 
            AND reservation_date = :reservation_date 
            AND ((start_time BETWEEN :start_time AND :end_time) 
            OR (end_time BETWEEN :start_time AND :end_time))
        ";
        $params = [
            ':court_id' => $court_id,
            ':reservation_date' => $reservation_date,
            ':start_time' => $start_time,
            ':end_time' => $end_time
        ];
        return Database::query($sql, $params)->fetchColumn() == 0;
    }

    /**
     * @return array
     */
    public static function getAllReservations(): array
    {
        $sql = "SELECT cr.*, u.last_name AS member_name, c.court_name 
                FROM COURT_RESERVATION cr
                JOIN MEMBER u ON cr.member_id = u.member_id
                JOIN COURT c ON cr.court_id = c.court_id
                ORDER BY cr.reservation_date DESC";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public static function getAllCourts(): array
    {
        $sql = "SELECT * FROM COURT";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $member_id
     * @param $court_id
     * @param $reservation_date
     * @param $start_time
     * @param $end_time
     * @return bool
     */
    public static function addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time): bool
    {
        $sql = "INSERT INTO COURT_RESERVATION (member_id, court_id, reservation_date, start_time, end_time)
                VALUES (:member_id, :court_id, :reservation_date, :start_time, :end_time)";
        $params = [
            ':member_id' => $member_id,
            ':court_id' => $court_id,
            ':reservation_date' => $reservation_date,
            ':start_time' => $start_time,
            ':end_time' => $end_time
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $reservation_id
     * @return bool
     */
    public static function deleteReservation($reservation_id): bool
    {
        $sql = "DELETE FROM COURT_RESERVATION WHERE reservation_id = :reservation_id";
        $params = [':reservation_id' => $reservation_id];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $reservation_id
     * @return mixed
     */
    public static function getReservationById($reservation_id): mixed
    {
        $sql = "SELECT * FROM COURT_RESERVATION WHERE reservation_id = :reservation_id";
        $params = ['reservation_id' => $reservation_id];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $reservation_id
     * @param $reservation_date
     * @param $start_time
     * @param $end_time
     * @param $event_id
     * @return bool
     */
    public static function updateReservation($reservation_id, $reservation_date, $start_time, $end_time, $event_id): bool
    {
        $sql = "UPDATE COURT_RESERVATION SET reservation_date = ?, start_time = ?, end_time = ?, event_id = ? WHERE reservation_id = ?";
        $params = [$reservation_date, $start_time, $end_time, $event_id, $reservation_id];
        return Database::query($sql, $params)->rowCount() > 0;
    }

}

?>