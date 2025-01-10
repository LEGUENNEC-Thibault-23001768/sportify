<?php

namespace Controllers;

use Core\APIController;
use Core\APIResponse;
use Models\Booking;
use Models\User;
use DateTime;

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

    public function delete($reservation_id = null)
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];


        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }

        $user = User::getUserById($currentUserId);
        $reservation = Booking::getReservationById($reservation_id);

        if (!$reservation) {
            return $response->setStatusCode(404)->setData(['error' => 'Reservation not found'])->send();
        }

        if ($reservation['member_id'] != $currentUserId && $user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized to delete this reservation'])->send();
        }

        Booking::deleteReservation($reservation_id);

        return $response->setStatusCode(200)->setData(['message' => 'Reservation deleted successfully'])->send();
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
        
        $startTime = new DateTime($reservation_date . ' ' . $start_time);
        $endTime = new DateTime($reservation_date . ' ' . $end_time);
        $duration = $endTime->diff($startTime);
        
        $totalHours = $duration->h + ($duration->i / 60);

        if ($totalHours > 2) {
              return $response->setStatusCode(400)->setData(['error' => 'Reservation cannot exceed 2 hours'])->send();
        }

        Booking::addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time);

        return $response->setStatusCode(201)->setData(['message' => 'Reservation created successfully'])->send();
    }
    
    public function put($reservationId = null) {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);;

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return $response->setStatusCode(400)->setData(['error' => 'Invalid JSON data'])->send();
        }

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

        $startTime = new DateTime($reservation_date . ' ' . $start_time);
        $endTime = new DateTime($reservation_date . ' ' . $end_time);
        $duration = $endTime->diff($startTime);
        
        $totalHours = $duration->h + ($duration->i / 60);

        if ($totalHours > 2) {
              return $response->setStatusCode(400)->setData(['error' => 'Reservation cannot exceed 2 hours'])->send();
        }

        Booking::updateReservation($reservationId, $reservation_date, $start_time, $end_time);

        return $response->setStatusCode(200)->setData(['message' => 'Reservation updated successfully'])->send();
    }

}