<?php

namespace Models;

use Core\Database;
use Models\Court;
use Models\User;
use PDO;

class Booking {

    public static function isAvailable($court_id, $reservation_date, $start_time, $end_time) {
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

    public static function getAllReservations()
    {
        $sql = "SELECT cr.*, u.last_name AS member_name, c.court_name 
                FROM COURT_RESERVATION cr
                JOIN MEMBER u ON cr.member_id = u.member_id
                JOIN COURT c ON cr.court_id = c.court_id
                ORDER BY cr.reservation_date DESC";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllCourts()
    {
        $sql = "SELECT * FROM COURT";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time)
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

    public static function deleteReservation($reservation_id)
    {
        $sql = "DELETE FROM COURT_RESERVATION WHERE reservation_id = :reservation_id";
        $params = [':reservation_id' => $reservation_id];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public function getReservationById($reservation_id) {
        $sql = "SELECT * FROM COURT_RESERVATION WHERE reservation_id = :reservation_id";
        $params = ['reservation_id' => $reservation_id];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);    
    }

    public function updateReservation($reservation_id, $reservation_date, $start_time, $end_time) {
        $sql = "UPDATE COURT_RESERVATION SET reservation_date = ?, start_time = ?, end_time = ? WHERE reservation_id = ?";
        $params = [$reservation_date, $start_time, $end_time, $reservation_id];
        return Database::query($sql, $params)->rowCount() > 0;
    }
    
}
?>