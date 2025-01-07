<?php

namespace Controllers;

use Core\APIResponse;
use Core\View;
use Exception;
use Models\Booking;
use Models\User;

class BookingController
{

    private View $view;
    private User $userModel;
    private Booking $bookingModel;

    private View $view;
    private User $userModel;
    private Booking $bookingModel;

    public function __construct()
    {
        $this->view = new View();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function index(): void

    {

        $bookingModel = new Booking();

        session_start();
        $currentUserId = $_SESSION['user_id'];

        if (!$currentUserId) {
            header('Location: /login');
            return;
        }

        $user = $this->userModel->getUserById($currentUserId);

        $bookings = $bookingModel->getAllReservations();
        $courts = $bookingModel->getAllCourts();

        echo $this->view->render('dashboard/booking/index', [
            'bookings' => $bookings,
            'user' => $user
        ]);
    }

    /**
     * @return void
     */
    public function store(): void

    {
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
        }
    }


    /**
     * @param $reservation_id
     * @return void
     */
    public function delete($reservation_id): void
    {
        session_start();
        $currentUserId = $_SESSION['user_id'];

        if (!$currentUserId) {
            header('Location: /login');
            return;
        }

        $user = $this->userModel->getUserById($currentUserId);
        $reservation = $this->bookingModel->getReservationById($reservation_id);

        if (!$reservation) {
            $_SESSION['error'] = 'Réservation introuvable.';
            header('Location: /dashboard/booking');
            return;
        }

        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            $_SESSION['error'] = 'Vous n\'avez pas les droits pour supprimer cette réservation.';
            header('Location: /dashboard/booking');
            return;
        }

        $this->bookingModel->deleteReservation($reservation_id);
        $_SESSION['success'] = 'Réservation supprimée avec succès.';
        header('Location: /dashboard/booking');
    }

    /**
     * @param $reservation_id
     * @return void
     * @throws Exception
     */
    public function edit($reservation_id): void
    {
        session_start();
        $currentUserId = $_SESSION['user_id'];

        if (!$currentUserId) {
            header('Location: /login');
            return;
        }

        $reservation = $this->bookingModel->getReservationById($reservation_id);
        $user = $this->userModel->getUserById($currentUserId);

        //var_dump($reservation);

        if (!$reservation) {
            $_SESSION['error'] = 'Réservation introuvable.';
            header('Location: /dashboard/booking');
            return;
        }

        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            $_SESSION['error'] = 'Vous n\'avez pas les droits pour modifier cette réservation.';
            header('Location: /dashboard/booking');
            return;
        }


        echo $this->view->render('dashboard/booking/edit', ['reservation' => $reservation, 'user' => $user]);
    }

    /**
     * @param $reservation_id
     * @return void
     */
    public function update($reservation_id): void
    {
        session_start();
        $currentUserId = $_SESSION['user_id'];

        if (!$currentUserId) {
            header('Location: /login');
            return;
        }

        $reservation = $this->bookingModel->getReservationById($reservation_id);

        if (!$reservation) {
            $_SESSION['error'] = 'Réservation introuvable.';
            header('Location: /dashboard/booking');
            return;
        }

        $user = $this->userModel->getUserById($currentUserId);
        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            $_SESSION['error'] = 'Vous n\'avez pas les droits pour modifier cette réservation.';
            header('Location: /dashboard/booking');
            return;
        }

        $reservation_date = $_POST['reservation_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        if (empty($reservation_date) || empty($start_time) || empty($end_time)) {
            $_SESSION['error'] = 'Tous les champs sont requis.';
            header("Location: /dashboard/booking/{$reservation_id}/edit");
            return;
        }

        Booking::updateReservation($reservation_id, $reservation_date, $start_time, $end_time, null);
        $_SESSION['success'] = 'Réservation mise à jour avec succès.';
        header('Location: /dashboard/booking');
    }

    public function getBookings()
    {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
        $bookings = Booking::getAllReservations();

//        return $bookings;
        $mobiscrollBookings = [];
        foreach ($bookings as $booking) {

            $mobiscrollBookings[] = [
                'type' => 'booking',
                'id' => "booking_" . $booking['reservation_id'],
                'title' => $booking['court_name'],
                'start' => $booking['reservation_date'] . 'T' . $booking['start_time'],
                'end' => $booking['reservation_date'] . 'T' . $booking['end_time'],
                'location' => $booking['court_name'],
                'created_by' => $booking['member_id'],
                'color' => 'orange',
//                'is_registered' => EventRegistration::isUserRegistered($booking['event_id'], $currentUserId),
            ];
        }

        $response = new APIResponse();
        $response->setStatusCode(200)->setData($mobiscrollBookings)->send();
    }

}
