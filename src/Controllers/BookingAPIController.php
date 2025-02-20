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
use Core\Database; 
use PDO;

class BookingAPIController extends APIController implements RouteProvider
{
    public static function routes(): void
    {
        Router::apiResource('/api/booking', self::class, Auth::requireLogin());
        Router::get('/api/booking/available-hours', self::class . '@getAvailableHours', Auth::requireLogin());
        Router::get('/api/members/search', self::class . '@search', Auth::requireLogin());
        Router::get('/api/members/{member_id}', self::class . '@getMember', Auth::requireLogin());
    }

    public function search() {
        $response = new APIResponse();
        $searchTerm = $_GET['term'] ?? '';
        $users = User::searchMembers($searchTerm);
    
        if ($users === false) {
            return $response->setStatusCode(500)->setData(['error' => 'Erreur lors de la recherche des membres'])->send();
        }
    
        // Ajoutez ceci pour vérifier la structure des données avant de les renvoyer :
        error_log(print_r($users, true));  // Affiche le contenu de $users dans les logs PHP
    
        return $response->setStatusCode(200)->setData($users)->send();
    }

    public function getMember($member_id = null){
        $response = new APIResponse();
        $member = User::find($member_id);
   
        if (!$member) {
             return $response->setStatusCode(404)->setData(['error' => 'Member not found'])->send();
        }
   
        return $response->setStatusCode(200)->setData($member)->send();
   
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
        $start_times = explode(',', $data['start_time']); 
        $reservation_type = $data['reservation_type'];
        $team_members = $data['team_members'] ?? [];  // Récupère les membres de l'équipe
        $team_name = $data['team_name'] ?? null;

        if (empty($member_id) || empty($court_id) || empty($reservation_date) || empty($start_times)) {
            return $response->setStatusCode(400)->setData(['error' => 'Missing required fields'])->send();
        }

        $pdo = Database::getConnection(); // Utilisation directe de la connexion PDO

        try {

            $team_id = null;

// Code de vérification de la capacité de la salle
            $sqlCourt = "SELECT max_capacity FROM COURT WHERE court_id = :court_id";
            $stmtCourt = $pdo->prepare($sqlCourt);
            $stmtCourt->execute(['court_id' => $court_id]);
            $court = $stmtCourt->fetch(PDO::FETCH_ASSOC);
// Throw if missing values on courts
         if (!is_array($court)  || empty($court['max_capacity'])){
              error_log("missing  MaxCapacity ". json_encode($court));
                 throw new \Exception("Erreur lors du capMaxCapacity");
               }


            $maxCapacity = (int)$court['max_capacity'];
            error_log(' maxcap teamM  '. count($team_members) . " VS ". $maxCapacity);
            if (count($team_members) > $maxCapacity) {
                return $response->setStatusCode(400)->setData(['error' => "La capacité maximale pour cette salle est atteinte."])->send();
               
                  }
                //
                if ($reservation_type === 'team') {
                    if (empty($team_name)) {
                        throw new \Exception("Le nom de l'équipe est requis pour les réservations en équipe.");
                    }

                    // Créez l'équipe
                    $sqlTeam = "INSERT INTO TEAM (team_name) VALUES (:team_name)";
                    $stmtTeam = $pdo->prepare($sqlTeam);
                    if (!$stmtTeam->execute(['team_name' => $team_name])) {
                        throw new \Exception("Erreur lors de la création de l'équipe : " . json_encode($stmtTeam->errorInfo()));
                    }

                    // Récupérez l'ID de l'équipe créée
                    $team_id = $pdo->lastInsertId();

                    if (!$team_id) {
                        throw new \Exception("Erreur lors de la création de l'équipe : ID non généré.");
                    }

                    // Insérez les participants dans TEAM_PARTICIPANT
                    foreach ($team_members as $team_member_id) {
                        $sqlPart = "INSERT INTO TEAM_PARTICIPANT (team_id, member_id) VALUES (:team_id, :member_id)";
                        $stmtPart = $pdo->prepare($sqlPart);

                        $paramsPart = [
                            ':team_id' => $team_id,
                            ':member_id' => $team_member_id
                        ];

                        if (!$stmtPart->execute($paramsPart)) {
                            throw new \Exception("Erreur lors de l'ajout du membre à l'équipe : " . json_encode($stmtPart->errorInfo()));
                        }
                    }
                }

  foreach ($start_times as $start_time) {
    $end_time = date('H:i', strtotime($start_time . ' +1 hour'));
       if ($end_time === false) {
                  throw new \Exception("Erreur lors du calcul de l'heure de fin.");
                 }
       $sqlReservation = "INSERT INTO COURT_RESERVATION (member_id, court_id, reservation_date, start_time, end_time, team_id) VALUES (:member_id, :court_id, :reservation_date, :start_time, :end_time, :team_id)";

               $params = [
                       ':member_id' => $member_id,
                       ':court_id' => $court_id,
                       ':reservation_date' => $reservation_date,
                       ':start_time' => $start_time,
                       ':end_time' => $end_time,
                       ':team_id' => $team_id, 
                     ];

     $stmtReserve = $pdo->prepare($sqlReservation);
                    if (! $stmtReserve->execute($params)){
                 throw new \Exception("Create Reservation Failed." .json_encode($stmtReserve->errorInfo()));; 
                       }
               
}         return $response->setStatusCode(201)->setData(['message' => 'Reservation created successfully'])->send();
    }  catch (\Exception $e) {
            error_log("Error 08: " . $e->getMessage());  // Log the error for debugging
        // You might want to log detailed exception information for debugging purposes:
        // error_log("Database exception: " . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine());

        // Format the error info to human read for API return
        return $response->setStatusCode(500)->setData(['error' => $e->getMessage()])->send();
    }
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

        Booking::updateReservation($reservationId, $reservation_date, $start_time, $end_time, $reservation["event_id"]);

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