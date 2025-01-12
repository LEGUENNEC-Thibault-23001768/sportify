<?php

namespace Controllers;

use Core\APIController;
use Core\APIResponse;
use Models\Booking;
use Models\User;
use DateTime;
use Core\Router;
use Core\RouteProvider;
use Core\Auth;

class BookingAPIController extends APIController implements RouteProvider
{
    public static function routes(): void
    {
        Router::apiResource('/api/booking', self::class, Auth::requireLogin());
        Router::get('/api/booking/available-hours', self::class . '@getAvailableHours', Auth::requireLogin());
    }

   public function get($reservationId = null) {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
    
        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }
    
        if ($reservationId === null) {
            $user = User::getUserById($currentUserId);
            $bookings = Booking::getAllReservations();
    
            return $response->setStatusCode(200)->setData([
                'bookings' => $bookings
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
    
    public function post()
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $data = $_POST;

        if (!$currentUserId) {
            return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
        }
    
        $member_id = $currentUserId;
        $court_id = $data['court_id'];
        $reservation_date = $data['reservation_date'];
        $start_times = $data['start_time'];
    
        if (empty($member_id) || empty($court_id) || empty($reservation_date) || empty($start_times)) {
             return $response->setStatusCode(400)->setData(['error' => 'Missing required fields'])->send();
        }
         $start_times_array = explode(',', $start_times); 
    
            foreach ($start_times_array as $start_time) { 
    
                $end_time = date('H:i', strtotime($start_time . ' +1 hour')); 
                if ($end_time === false) {
                     return $response->setStatusCode(400)->setData(['error' => "Erreur lors du calcul de l'heure de fin."])->send();
                }
                $startTime = new DateTime($reservation_date . ' ' . $start_time);
                $endTime = new DateTime($reservation_date . ' ' . $end_time);
                $duration = $endTime->diff($startTime);
                $totalHours = $duration->h + ($duration->i / 60);
        
                if ($totalHours > 2) {
                      return $response->setStatusCode(400)->setData(['error' => 'Reservation cannot exceed 2 hours'])->send();
                }
        
                Booking::addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time);
            }
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
    public function getAvailableHours() {
        $response = new APIResponse();
        $courtId = $_GET['court_id'] ?? null;
        $date = $_GET['date'] ?? null;
         
        if (empty($courtId) || empty($date)) {
            return $response->setStatusCode(400)->setData(['error' => 'Missing court_id or date'])->send();
        }
        $today = new DateTime();
        $selectedDate = new DateTime($date);
        if ($selectedDate < $today->setTime(0,0,0)) {
            return $response->setStatusCode(400)->setData(['error' => 'Cannot select a date in the past'])->send();
        }


        try {
            $pdo = \Core\Database::getConnection();
             $stmt = $pdo->prepare("SELECT cr.start_time, cr.end_time, cr.reservation_date, c.court_name, m.last_name as member_name FROM COURT_RESERVATION cr JOIN COURT c ON cr.court_id = c.court_id JOIN MEMBER m ON cr.member_id = m.member_id WHERE cr.court_id = :court_id AND cr.reservation_date = :date");
            $stmt->execute(['court_id' => $courtId, 'date' => $date]);
            $bookings = $stmt->fetchAll();
            return $response->setStatusCode(200)->setData($bookings)->send();

        } catch (\Exception $e) {
            return $response->setStatusCode(500)->setData(['error' => 'A server error occurred.'])->send();
        }
    }
}