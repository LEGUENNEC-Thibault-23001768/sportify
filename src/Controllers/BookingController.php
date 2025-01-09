<?php

namespace Controllers;


use Core\View;
use Models\Booking;
use Models\User;
use Core\Database;


class BookingController {


    public function __construct()
    {
        $this->view = new View();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
    }

    public function index() {

        $bookingModel = new Booking();

        session_start();
        $currentUserId = $_SESSION['user_id'];

        if (!$currentUserId) {
            header('Location: /login');
            exit();
        }

        $user = $this->userModel->getUserById($currentUserId);

        $bookings = $bookingModel->getAllReservations();  
        $courts = $bookingModel->getAllCourts();

        echo $this->view->render('dashboard/booking/index', [
            'bookings' => $bookings,
            'user' => $user
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bookingModel = new Booking();
    
            $member_id = $_POST['member_id'] ?? null;
            $court_id = $_POST['court_id'] ?? null;
            $reservation_date = $_POST['reservation_date'] ?? null;
            $start_times = $_POST['start_time'] ?? null; 
            $duration = $_POST['duration'] ?? 1;
    
            if (empty($member_id) || empty($court_id) || empty($reservation_date) || empty($start_times)) {
                $_SESSION['error'] = "Tous les champs sont requis.";
                header('Location: /dashboard/booking');
                exit;
            }
    
            $start_times_array = explode(',', $start_times); 
    
            foreach ($start_times_array as $start_time) { 
                if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $start_time)) {
                    $_SESSION['error'] = "Format d'heure invalide.";
                    header('Location: /dashboard/booking');
                    exit;
                }
    
                $end_time = date('H:i', strtotime($start_time . ' +1 hour')); 
                if ($end_time === false) {
                    $_SESSION['error'] = "Erreur lors du calcul de l'heure de fin.";
                    header('Location: /dashboard/booking');
                    exit;
                }
    
                $bookingModel->addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time);
            }
    
            $_SESSION['success'] = "Réservation effectuée avec succès.";
            header('Location: /dashboard/booking');
            exit;
        }
    }

    public function delete($reservation_id) {
        session_start();
        $currentUserId = $_SESSION['user_id'];
    
        if (!$currentUserId) {
            header('Location: /login');
            exit();
        }
    
        $user = $this->userModel->getUserById($currentUserId);
        $reservation = $this->bookingModel->getReservationById($reservation_id);
    
        if (!$reservation) {
            $_SESSION['error'] = 'Réservation introuvable.';
            header('Location: /dashboard/booking');
            exit();
        }
    
        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            $_SESSION['error'] = 'Vous n\'avez pas les droits pour supprimer cette réservation.';
            header('Location: /dashboard/booking');
            exit();
        }
    
        $this->bookingModel->deleteReservation($reservation_id);
        $_SESSION['success'] = 'Réservation supprimée avec succès.';
        header('Location: /dashboard/booking');
        exit();
    }

    public function edit($reservation_id) {
        session_start();
        $currentUserId = $_SESSION['user_id'];
    
        if (!$currentUserId) {
            header('Location: /login');
            exit();
        }
    
        $reservation = $this->bookingModel->getReservationById($reservation_id);
        $user = $this->userModel->getUserById($currentUserId);
    

        if (!$reservation) {
            $_SESSION['error'] = 'Réservation introuvable.';
            header('Location: /dashboard/booking');
            exit();
        }
    
        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            $_SESSION['error'] = 'Vous n\'avez pas les droits pour modifier cette réservation.';
            header('Location: /dashboard/booking');
            exit();
        }
        
   
        echo $this->view->render('dashboard/booking/edit', ['reservation' => $reservation,'user' => $user]);
    }
    
    public function update($reservation_id) {
        session_start();
        $currentUserId = $_SESSION['user_id'];
    
        if (!$currentUserId) {
            header('Location: /login');
            exit();
        }
    
        $reservation = $this->bookingModel->getReservationById($reservation_id);
    
        if (!$reservation) {
            $_SESSION['error'] = 'Réservation introuvable.';
            header('Location: /dashboard/booking');
            exit();
        }
    
        $user = $this->userModel->getUserById($currentUserId);
        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            $_SESSION['error'] = 'Vous n\'avez pas les droits pour modifier cette réservation.';
            header('Location: /dashboard/booking');
            exit();
        }
    
        $reservation_date = $_POST['reservation_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
    
        if (empty($reservation_date) || empty($start_time) || empty($end_time)) {
            $_SESSION['error'] = 'Tous les champs sont requis.';
            header("Location: /dashboard/booking/{$reservation_id}/edit");
            exit();
        }
    
        $this->bookingModel->updateReservation($reservation_id, $reservation_date, $start_time, $end_time);
        $_SESSION['success'] = 'Réservation mise à jour avec succès.';
        header('Location: /dashboard/booking');
        exit();
    }

    public function getBookingsByCourtAndDate() {
        try {
            $courtId = $_GET['court_id'] ?? null;
            $date = $_GET['date'] ?? null;
            if (!isset($courtId) || !isset($date) || !is_numeric($courtId) || empty($date)) {
                header('Content-Type: application/json'); 
                http_response_code(400);
                echo json_encode(['error' => 'Données invalides : court_id doit être un nombre et la date ne peut pas être vide.']);
                exit;
            }

            $pdo = Database::getConnection();

            $stmt = $pdo->prepare("SELECT cr.start_time, cr.end_time, cr.reservation_date, c.court_name, m.last_name as member_name FROM COURT_RESERVATION cr JOIN COURT c ON cr.court_id = c.court_id JOIN MEMBER m ON cr.member_id = m.member_id WHERE cr.court_id = :court_id AND cr.reservation_date = :date");
            $stmt->execute(['court_id' => $courtId, 'date' => $date]);
            $bookings = $stmt->fetchAll();
            header('Content-Type: application/json');
            echo json_encode($bookings);
            exit;

        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des réservations : " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Une erreur serveur est survenue. Veuillez consulter les logs.']);
            exit;
        }
    }

    public function getReservations() {
        try {
            $bookings = Booking::getAllReservations();

            header('Content-Type: application/json');
            echo json_encode($bookings);
            exit;

        } catch (\Exception $e) {
            Log::error("Error fetching all reservations: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'A server error occurred.']);
            exit;
        }
    }

    public function getCourtName($court_id) {
        try {
            $court = $this->bookingModel->getCourtById($court_id);
            if ($court) {
                header('Content-Type: application/json');
                echo json_encode(['courtName' => $court['court_name']]);
                exit;
            } else {
                http_response_code(404); 
                echo json_encode(['error' => 'Court not found']);
                exit;
            }
        } catch (\Exception $e) {
            Log::error("Error fetching court name: " . $e->getMessage());
            http_response_code(500); 
            echo json_encode(['error' => 'Server error']);
            exit;
        }
    }
}
