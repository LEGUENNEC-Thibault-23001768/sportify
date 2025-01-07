<?php
namespace Controllers;

use Core\Database;
use Models\User; 
use Controllers\AuthController;


class TrainerController
{
    public function show($coachId)
{
    $pdo = Database::getConnection();

    $sql = "SELECT * FROM COACH WHERE coach_id = :coach_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['coach_id' => $coachId]);
    $trainer = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($trainer) {
        if (isset($_SESSION['user_id'])) {
            $memberId = $_SESSION['user_id'];
            $user = User::getUserById($memberId);  
        }

        header('Content-Type: application/json');
        echo json_encode([
            'trainer' => $trainer,
            'user' => isset($user) ? $user : null
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Trainer not found']);
    }
}


public function getReservations($coachId)
{
    $pdo = Database::getConnection();
    $sql = "SELECT 
                r.reservation_id, 
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
            'id' => $reservation['reservation_id'],
            'title' => $reservation['first_name'] . ' ' . $reservation['last_name'],
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

    $sqlConflict = "SELECT * FROM RESERVATION_HISTORY 
                    WHERE coach_id = :coach_id 
                    AND reservation_date = :reservation_date 
                    AND (
                        (start_time < :end_time AND end_time > :start_time)
                    )";
    $stmtConflict = $pdo->prepare($sqlConflict);
    $stmtConflict->execute([
        'coach_id' => $data['coach_id'],
        'reservation_date' => $data['reservation_date'],
        'start_time' => $data['start_time'],
        'end_time' => $data['end_time'],
    ]);
    $conflict = $stmtConflict->fetch(\PDO::FETCH_ASSOC);

    if ($conflict) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ce créneau est déjà réservé.']);
        return;
    }

    try {
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
public function deleteReservation($reservationId)
{
    $memberId = $_SESSION['user_id'];
    $pdo = Database::getConnection();

    try {
        $sql = "DELETE FROM RESERVATION_HISTORY 
                WHERE reservation_id = :reservationId AND member_id = :memberId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'reservationId' => $reservationId,
            'memberId' => $memberId,
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Réservation supprimée avec succès.']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Réservation introuvable ou accès non autorisé.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
    }
    exit; 
}


public function updateReservation($reservationId) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
        return;
    }

    $reservation_date = $data['reservation_date'];
    $start_time = $data['start_time'];
    $end_time = $data['end_time'];
    $color = $data['color'];
    $reservation_date = explode('T', $reservation_date)[0]; 
    $start_time = explode('T', $start_time)[1]; 
    $end_time = str_pad($end_time, 5, ':00');
    $reservation_id = $reservationId; 
    $pdo = Database::getConnection();
    $query = "UPDATE RESERVATION_HISTORY SET 
                reservation_date = :reservation_date, 
                start_time = :start_time, 
                end_time = :end_time, 
                color = :color 
              WHERE reservation_id = :reservation_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':reservation_date', $reservation_date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':color', $color);
    $stmt->bindParam(':reservation_id', $reservation_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Réservation mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
}


}
