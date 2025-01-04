<?php

namespace Models;
use Core\Database;

class TrainerModel
{
    public static function getAllReservations()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT r.*, u.name AS coach_name 
                               FROM reservations r
                               JOIN users u ON r.coach_id = u.id");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

