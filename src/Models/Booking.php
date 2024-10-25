<?php

namespace Models;

use Core\Database;
use Models\Court;
use Models\User;
use PDO;

class Booking {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function isAvailable($court_id, $reservation_date, $start_time, $end_time) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM COURT_RESERVATION 
            WHERE court_id = :court_id 
            AND reservation_date = :reservation_date 
            AND ((start_time BETWEEN :start_time AND :end_time) 
            OR (end_time BETWEEN :start_time AND :end_time))
        ");
        $stmt->execute([
            'court_id' => $court_id,
            'reservation_date' => $reservation_date,
            'start_time' => $start_time,
            'end_time' => $end_time
        ]);
        return $stmt->fetchColumn() == 0;
    }

    public function getAllReservations()
    {
        $sql = "SELECT cr.*, u.last_name AS member_name, c.court_name 
                FROM COURT_RESERVATION cr
                JOIN MEMBER u ON cr.member_id = u.member_id
                JOIN COURT c ON cr.court_id = c.court_id
                ORDER BY cr.reservation_date DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Récupérer toutes les salles de sport
    public function getAllCourts()
    {
        $sql = "SELECT * FROM COURT";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Ajouter une nouvelle réservation
    public function addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time)
    {
        $sql = "INSERT INTO COURT_RESERVATION (member_id, court_id, reservation_date, start_time, end_time)
                VALUES (:member_id, :court_id, :reservation_date, :start_time, :end_time)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':member_id' => $member_id,
            ':court_id' => $court_id,
            ':reservation_date' => $reservation_date,
            ':start_time' => $start_time,
            ':end_time' => $end_time
        ]);
    }

    // Supprimer une réservation
    public function deleteReservation($reservation_id)
    {
        $sql = "DELETE FROM COURT_RESERVATION WHERE reservation_id = :reservation_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':reservation_id' => $reservation_id]);
    }



}
?>