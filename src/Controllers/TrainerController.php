<?php

namespace Controllers;

use Core\Database;
use Core\APIResponse;
use Models\User;
use Models\TrainerModel;

class TrainerController
{
    public function show($coachId)
    {
        $pdo = Database::getConnection();
    
        $sql = "SELECT * FROM COACH WHERE coach_id = :coach_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['coach_id' => $coachId]);
        $trainer = $stmt->fetch(\PDO::FETCH_ASSOC);
    
        // Débogage : Vérifiez si $trainer contient des données
        if ($trainer) {
            header('Content-Type: application/json');
            echo json_encode($trainer);
        } else {
            // Ajouter une sortie claire pour les erreurs
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Trainer not found']);
        }
    }

    // TrainerController.php
    public function getReservations($coachId) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT member_id, reservation_date, start_time, end_time FROM RESERVATION_HISTORY WHERE coach_id = :coach_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['coach_id' => $coachId]);
        $reservations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $events = array_map(function($reservation) {
            return [
                'title' => 'Réservation de ' . $reservation['member_id'],
                'start'=> $reservation['reservation_date'] . 'T' . $reservation['start_time'],
                'end' => $reservation['reservation_date'] . 'T' . $reservation['end_time'],
                'color' => '#4981d6'
            ];
        }, $reservations);
        
        header('Content-Type: application/json');
        echo json_encode($events);
    }
    

    



public function saveReservation()
{
    session_start(); 

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); 
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
        return;
    }

    $memberId = $_SESSION['user_id']; 
    $pdo = Database::getConnection();

    $data = json_decode(file_get_contents('php://input'), true);

    if (
        empty($data['activity']) || 
        empty($data['reservation_date']) || 
        empty($data['start_time']) || 
        empty($data['end_time']) || 
        empty($data['coach_id'])
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
        return;
    }

    $reservationDateTime = strtotime($data['reservation_date'] . ' ' . $data['start_time']);
    if ($reservationDateTime < time()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Impossible de réserver une date passée.']);
        return;
    }

    try {
        // Enregistrement de la réservation dans la table RESERVATION_HISTORY
        $sql = "INSERT INTO RESERVATION_HISTORY 
                (member_id, activity, reservation_date, start_time, end_time, coach_id) 
                VALUES (:member_id, :activity, :reservation_date, :start_time, :end_time, :coach_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'member_id' => $memberId,
            'activity' => $data['activity'],
            'reservation_date' => $data['reservation_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'coach_id' => $data['coach_id']
        ]);

        // Retourner la nouvelle réservation dans le format attendu pour le calendrier
        $newReservation = [
            'title' => $data['activity'],
            'start' => $data['reservation_date'] . 'T' . $data['start_time'],
            'end' => $data['reservation_date'] . 'T' . $data['end_time'],
            'color' => '#4981d6', // Définir la couleur d'événement (personnalisable)
            'description' => 'Réservé par : ' . $memberId
        ];

        // Retourner les données de la réservation avec un succès
        echo json_encode(['success' => true, 'message' => 'Réservation enregistrée avec succès.', 'reservation' => $newReservation]);
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()]);
    }
}



    
}
