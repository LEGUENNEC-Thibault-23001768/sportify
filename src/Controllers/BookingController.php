<?php

namespace Controllers;


use Core\View;
use Models\Booking;
use Models\User;


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
    
            error_log("Début de la fonction store()");
    
            $member_id = $_POST['member_id'] ?? null;
            $court_id = $_POST['court_id'] ?? null;
            $reservation_date = $_POST['reservation_date'] ?? null;
            $start_time = $_POST['start_time'] ?? null;
            $duration = $_POST['duration'] ?? 1;
    
            error_log("Données reçues : ");
            error_log("member_id: " . ($member_id ?? 'NULL')); // Use null coalescing for logging as well
            error_log("court_id: " . ($court_id ?? 'NULL'));
            error_log("reservation_date: " . ($reservation_date ?? 'NULL'));
            error_log("start_time: " . ($start_time ?? 'NULL'));
            error_log("duration: " . ($duration ?? 'NULL'));
    
    
            if (empty($member_id) || empty($court_id) || empty($reservation_date) || empty($start_time)) {
                error_log("Erreur : au moins un champ obligatoire est vide.");
                $_SESSION['error'] = "Tous les champs sont requis."; // Set session error
                header('Location: /dashboard/booking'); // Redirect to booking page
                exit; // Stop further execution
            }
    
            // Validate start_time format before using strtotime
            if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $start_time)) {
                error_log("Erreur : Format de start_time invalide.");
                $_SESSION['error'] = "Format d'heure invalide.";
                header('Location: /dashboard/booking');
                exit;
            }
    
            $end_time = date('H:i', strtotime($start_time . ' +' . $duration . ' hour'));
    
            if ($end_time === false) {
                error_log("Erreur : Calcul de end_time a échoué.");
                $_SESSION['error'] = "Erreur lors du calcul de l'heure de fin.";
                header('Location: /dashboard/booking');
                exit;
            }
    
            error_log("end_time (générée): " . $end_time);
    
            $bookingModel->addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time);
    
            error_log("Réservation ajoutée avec succès.");
            $_SESSION['success'] = "Réservation effectuée avec succès."; // Set success message
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
    
        //var_dump($reservation);

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

}
