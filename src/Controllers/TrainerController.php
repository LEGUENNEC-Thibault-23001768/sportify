<?php
namespace Controllers;

use Core\Database;
use Models\User; 

class TrainerController
{
    public function show($coachId)
    {
        $pdo = Database::getConnection();
    
        // Récupérer les informations du coach
        $sql = "SELECT * FROM COACH WHERE coach_id = :coach_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['coach_id' => $coachId]);
        $trainer = $stmt->fetch(\PDO::FETCH_ASSOC);
    
        // Vérifier si le coach existe
        if ($trainer) {
            // Vérifier si l'utilisateur est connecté
            session_start();
            $user = null;
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                $user = User::getUserById($userId); // Utiliser la méthode du modèle User
            }

            // Retourner les données du coach et de l'utilisateur dans la réponse JSON
            header('Content-Type: application/json');
            echo json_encode([
                'trainer' => $trainer,
                'user' => $user // Passer les données de l'utilisateur (null si non connecté)
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Trainer not found']);
        }
    }

    public function getReservations($coachId)
    {
        $pdo = Database::getConnection();

        // Ajouter une jointure pour récupérer les noms et prénoms des membres
        $sql = "SELECT 
                    r.activity, 
                    r.reservation_date, 
                    r.start_time, 
                    r.end_time, 
                    r.color, 
                    m.first_name, 
                    m.last_name 
                FROM RESERVATION_HISTORY r
                JOIN MEMBER m ON r.member_id = m.member_id
                WHERE r.coach_id = :coach_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['coach_id' => $coachId]);
        $reservations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $events = array_map(function($reservation) {
            return [
                'title' => '' . $reservation['first_name'] . ' ' . $reservation['last_name'],
                'start' => $reservation['reservation_date'] . 'T' . $reservation['start_time'],
                'end' => $reservation['reservation_date'] . 'T' . $reservation['end_time'],
                'color' => $reservation['color'],
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
            empty($data['coach_id']) || 
            empty($data['color'])
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
            // Enregistrer la réservation
            $sql = "INSERT INTO RESERVATION_HISTORY 
                    (member_id, activity, reservation_date, start_time, end_time, coach_id, color) 
                    VALUES (:member_id, :activity, :reservation_date, :start_time, :end_time, :coach_id, :color)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'member_id' => $memberId,
                'activity' => $data['activity'],
                'reservation_date' => $data['reservation_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'coach_id' => $data['coach_id'],
                'color' => $data['color'],
            ]);

            $newReservation = [
                'title' => $data['activity'],
                'start' => $data['reservation_date'] . 'T' . $data['start_time'],
                'end' => $data['reservation_date'] . 'T' . $data['end_time'],
                'color' => $data['color'],
            ];

            echo json_encode(['success' => true, 'message' => 'Réservation enregistrée avec succès.', 'reservation' => $newReservation]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()]);
        }
    }
}
