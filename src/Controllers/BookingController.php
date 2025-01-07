<?php

namespace Controllers;

use Core\APIController;
use Core\APIResponse;
use Models\Booking;
use Models\User;

class BookingController extends APIController
{

    public function get($reservationId = null) {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
    
        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }
    
        if ($reservationId === null) {
            // Logic for getting all reservations
            $user = User::getUserById($currentUserId);
            $bookings = Booking::getAllReservations();
    
            return $response->setStatusCode(200)->setData([
                'bookings' => $bookings,
                'user' => $user
            ])->send();
        } else {
            // Logic for getting a specific reservation
            $reservation = Booking::getReservationById($reservationId);
            $user = User::getUserById($currentUserId);
    
            if (!$reservation) {
                return $response->setStatusCode(404)->setData(['error' => 'Reservation not found'])->send();
            }
    
            if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
                return $response->setStatusCode(403)->setData(['error' => 'User not authorized to access this reservation'])->send();
            }
    
            return $response->setStatusCode(200)->setData(['reservation' => $reservation])->send();
        }
    }

    public function index()
    {
        return $this->handleRequest($_SERVER['REQUEST_METHOD']);
    }

    public function edit($reservationId)
    {
        return $this->handleRequest($_SERVER['REQUEST_METHOD'], $reservationId);
    }

    public function update($reservationId)
    {
        return $this->handleRequest($_SERVER['REQUEST_METHOD'], $reservationId);
    }

    public function store()
    {
        return $this->handleRequest($_SERVER['REQUEST_METHOD']);
    }

    public function delete($reservation_id = null)
    {
        return $this->handleRequest($_SERVER['REQUEST_METHOD'],$reservation_id);
    }
    
    public function post() {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $data = $_POST;

        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }

        $member_id = $currentUserId; 
        $court_id = $data['court_id'];
        $reservation_date = $data['reservation_date'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];

        if (empty($member_id) || empty($court_id) || empty($reservation_date) || empty($start_time) || empty($end_time)) {
            return $response->setStatusCode(400)->setData(['error' => 'Missing required fields'])->send();
        }

        Booking::addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time);

        return $response->setStatusCode(201)->setData(['message' => 'Reservation created successfully'])->send();
    }
    
    public function put($reservationId = null) {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $data = $_POST;

        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }

        $reservation = Booking::getReservationById($reservationId);

        if (!$reservation) {
            return $response->setStatusCode(404)->setData(['error' => 'Reservation not found'])->send();
        }

        $user = User::getUserById($currentUserId);
        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized to modify this reservation'])->send();
        }

        $reservation_date = $data['reservation_date'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];

        if (empty($reservation_date) || empty($start_time) || empty($end_time)) {
            return $response->setStatusCode(400)->setData(['error' => 'Missing required fields'])->send();
        }

        Booking::updateReservation($reservationId, $reservation_date, $start_time, $end_time);

        return $response->setStatusCode(200)->setData(['message' => 'Reservation updated successfully'])->send();
    }

    public function postDelete($reservationId = null)
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];

        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }

        $user = User::getUserById($currentUserId);
        $reservation = Booking::getReservationById($reservationId);

        if (!$reservation) {
            return $response->setStatusCode(404)->setData(['error' => 'Reservation not found'])->send();
        }

        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized to delete this reservation'])->send();
        }

        Booking::deleteReservation($reservationId);

        return $response->setStatusCode(200)->setData(['message' => 'Reservation deleted successfully'])->send();
    }
}