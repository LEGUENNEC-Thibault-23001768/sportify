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
use Core\Config;

class BookingAPIController extends APIController implements RouteProvider
{
    public static function routes(): void
    {
        Router::apiResource('/api/booking', self::class, Auth::requireLogin());
        Router::get('/api/booking/available-hours', self::class . '@getAvailableHours', Auth::requireLogin());
        Router::get('/api/members/search', self::class . '@search', Auth::requireLogin());
        Router::get('/api/members/{member_id}', self::class . '@getMember', Auth::requireLogin());
        Router::get('/team-invitation/response', self::class . '@handleTeamInvitationResponse');
    }

    public function search() {
        $response = new APIResponse();
        $searchTerm = $_GET['term'] ?? '';
        $users = User::searchMembers($searchTerm);
    
        if ($users === false) {
            return $response->setStatusCode(500)->setData(['error' => 'Erreur lors de la recherche des membres'])->send();
        }

        error_log(print_r($users, true));  
    
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
        $team_members = $data['team_members'] ?? [];
        $team_name = $data['team_name'] ?? null;

        if (empty($member_id) || empty($court_id) || empty($reservation_date) || empty($start_times)) {
            return $response->setStatusCode(400)->setData(['error' => 'Missing required fields'])->send();
        }

        $pdo = Database::getConnection();

        try {
            $team_id = null;
            $sqlCourt = "SELECT max_capacity FROM COURT WHERE court_id = :court_id";
            $stmtCourt = $pdo->prepare($sqlCourt);
            $stmtCourt->execute(['court_id' => $court_id]);
            $court = $stmtCourt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($court)  || empty($court['max_capacity'])) {
                error_log("missing  MaxCapacity ". json_encode($court));
                throw new \Exception("Erreur lors du capMaxCapacity");
            }

            $maxCapacity = (int)$court['max_capacity'];
            error_log(' maxcap teamM  '. count($team_members) . " VS ". $maxCapacity);
            if (count($team_members) > $maxCapacity) {
                return $response->setStatusCode(400)->setData(['error' => "La capacité maximale pour cette salle est atteinte."])->send();
            }

            if ($reservation_type === 'team') {
                if (empty($team_name)) {
                    throw new \Exception("Le nom de l'équipe est requis pour les réservations en équipe.");
                }

                // Crée l'équipe
                $sqlTeam = "INSERT INTO TEAM (team_name) VALUES (:team_name)";
                $stmtTeam = $pdo->prepare($sqlTeam);
                if (!$stmtTeam->execute(['team_name' => $team_name])) {
                    throw new \Exception("Erreur lors de la création de l'équipe : " . json_encode($stmtTeam->errorInfo()));
                }

                $team_id = $pdo->lastInsertId();

                if (!$team_id) {
                    throw new \Exception("Erreur lors de la création de l'équipe : ID non généré.");
                }

                // Créer les invitations et envoyer les e-mails
                foreach ($team_members as $team_member_id) {
                    $token = bin2hex(random_bytes(32)); // Génère un token unique
                    $sqlInvite = "INSERT INTO TEAM_INVITATION (team_id, member_id, token) VALUES (:team_id, :member_id, :token)";
                    $stmtInvite = $pdo->prepare($sqlInvite);

                    $paramsInvite = [
                        ':team_id' => $team_id,
                        ':member_id' => $team_member_id,
                        ':token' => $token,
                    ];

                    if (!$stmtInvite->execute($paramsInvite)) {
                        throw new \Exception("Erreur lors de la création de l'invitation : " . json_encode($stmtInvite->errorInfo()));
                    }

                    // Envoie l'e-mail d'invitation
                    $member = User::find($team_member_id);
                    if ($member) {
                        $this->sendTeamInvitationEmail($member['email'], $team_id, $token, $team_name);
                    } else {
                        error_log("Membre introuvable avec l'ID : " . $team_member_id);
                    }
                }

                //Retourne l'ID de l'équipe et indique que les invitations ont étés envoyées
                 return $response->setStatusCode(200)->setData(['message' => 'Invitations envoyées. L\'équipe sera créée une fois que les membres auront répondu.', 'team_id' => $team_id])->send();
            } else {
              // Réservation individuelle
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
                      ':team_id' => null, // Pas d'équipe pour une réservation individuelle
                  ];

                  $stmtReserve = $pdo->prepare($sqlReservation);
                  if (! $stmtReserve->execute($params)){
                      throw new \Exception("Create Reservation Failed." .json_encode($stmtReserve->errorInfo()));;
                  }
              }
              return $response->setStatusCode(201)->setData(['message' => 'Reservation created successfully'])->send();

            }

        } catch (\Exception $e) {
            error_log("Error 08: " . $e->getMessage());
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
            $pdo = Database::getConnection();
             $stmt = $pdo->prepare("SELECT cr.start_time, cr.end_time, cr.reservation_date, c.court_name, m.last_name as member_name FROM COURT_RESERVATION cr JOIN COURT c ON cr.court_id = c.court_id JOIN MEMBER m ON cr.member_id = m.member_id WHERE cr.court_id = :court_id AND cr.reservation_date = :date");
            $stmt->execute(['court_id' => $courtId, 'date' => $date]);
            $bookings = $stmt->fetchAll();
            return $response->setStatusCode(200)->setData($bookings)->send();

        } catch (\Exception $e) {
            return $response->setStatusCode(500)->setData(['error' => 'A server error occurred.'])->send();
        }
    }

    private function sendTeamInvitationEmail($email, $team_id, $token, $team_name)
    {
        $mail_parts = Config::get("mail_parts");
        $invitation_link = Config::get("server_url") . "/team-invitation/response?team_id=" . $team_id . "&token=" . $token . "&email=" . urlencode($email) . "&action=accept";
        $decline_link = Config::get("server_url") . "/team-invitation/response?team_id=" . $team_id . "&token=" . $token . "&email=" . urlencode($email) . "&action=decline";
        $title = "Invitation à rejoindre l'équipe " . $team_name . " sur Sportify";

        $paragraph = "Vous avez été invité à rejoindre l'équipe " . htmlspecialchars($team_name) . " pour une réservation sur Sportify.  Veuillez cliquer sur le lien ci-dessous pour accepter ou refuser l'invitation :
            <p><a href='" . htmlspecialchars($invitation_link) . "'>Accepter</a>  |  <a href='" . htmlspecialchars($decline_link) . "'>Refuser</a></p>";

        $mail_parts['mail_body'] = str_replace("[TITLE]", $title, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[PARAGRAPH]", $paragraph, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[VERIFY_URL]", htmlspecialchars($invitation_link), $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[ANCHOR]", "Accepter/Refuser l'invitation", $mail_parts['mail_body']);

        $subject = $title;

        $message = $mail_parts['mail_head'] .
            $mail_parts['mail_title'] .
            $mail_parts['mail_head_end'] .
            $mail_parts['mail_body'] .
            $mail_parts['mail_footer'];

        $headers = "From: sportify@alwaysdata.net\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (!mail($email, $subject, $message, $headers)) {
            error_log("Erreur d'envoi d'email d'invitation à $email: " . error_get_last());
        }
    }

    public function handleTeamInvitationResponse()
    {
        $response = new APIResponse();
        $team_id = $_GET['team_id'] ?? null;
        $token = $_GET['token'] ?? null;
        $email = $_GET['email'] ?? null;
        $action = $_GET['action'] ?? null; // 'accept' ou 'decline' (ajouté dans le lien)

        if (!$team_id || !$token || !$email) {
            return $response->setStatusCode(400)->setData(['error' => 'Paramètres manquants.'])->send();
        }

        $pdo = Database::getConnection();

        try {
            // 1. Vérifier l'invitation (team_id, token, email correspondent)
            $sqlCheck = "SELECT invitation_id, member_id FROM TEAM_INVITATION WHERE team_id = :team_id AND token = :token AND member_id = (SELECT member_id FROM MEMBER WHERE email = :email) AND status = 'pending'";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':team_id' => $team_id,
                ':token' => $token,
                ':email' => $email,
            ]);

            $invitation = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$invitation) {
                return $response->setStatusCode(404)->setData(['error' => 'Invitation invalide ou déjà traitée.'])->send();
            }

            $invitation_id = $invitation['invitation_id'];
            $member_id = $invitation['member_id'];

            // 2. Mettre à jour le statut de l'invitation (accepted/declined)
            $status = ($action === 'accept') ? 'accepted' : 'declined';
            $sqlUpdate = "UPDATE TEAM_INVITATION SET status = :status WHERE invitation_id = :invitation_id";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':status' => $status,
                ':invitation_id' => $invitation_id,
            ]);

             // 3. Si tout le monde a répondu ou si la réservation n'est plus possible (par exemple, date dépassée), finaliser la réservation

           $team_name = Booking::getTeamName($team_id);
            if (Booking::allInvitationsAnswered($team_id)) {
                // Enregistre les participants acceptés dans TEAM_PARTICIPANT et crée la réservation
                if ($status == 'accepted') {
                  Booking::addTeamParticipants($team_id);
                }
                 Booking::createReservationFromTeam($team_id);
            }

            $message = ($status === 'accepted') ? 'Invitation acceptée.' : 'Invitation refusée.';
            return $response->setStatusCode(200)->setData(['message' => $message])->send();

        } catch (\Exception $e) {
            error_log("Erreur lors de la gestion de l'invitation : " . $e->getMessage());
            return $response->setStatusCode(500)->setData(['error' => 'Erreur serveur : ' . $e->getMessage()])->send();
        }
    }

}