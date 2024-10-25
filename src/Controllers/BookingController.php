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

            $member_id = $_POST['member_id'];
            $court_id = $_POST['court_id'];
            $reservation_date = $_POST['reservation_date'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
    

            if (empty($member_id) || empty($court_id) || empty($reservation_date) || empty($start_time) || empty($end_time)) {
                echo "noob";
                return;
            }
            
            var_dump($_POST);

            $bookingModel->addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time);
    
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
