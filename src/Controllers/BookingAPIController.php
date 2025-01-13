<?php

namespace Controllers;
use Core\APIController;
use Core\APIResponse;
use Models\Booking;
use Models\User;
use Models\Court;
use Models\Team;
use Models\TeamParticipant;// l'implementation du  model necessaire : j'avoue que  cela etais de mon coté ou pas, la source des pb, sur une requetes
use DateTime;
use Core\Router;
use Core\RouteProvider;
use Core\Auth;

 class BookingAPIController extends APIController implements RouteProvider {

       public static function routes(): void {
           Router::apiResource('/api/booking', self::class, Auth::requireLogin());
            Router::get('/api/booking/available-hours', self::class . '@getAvailableHours', Auth::requireLogin());
           Router::post('/api/team/booking', self::class . '@teamBooking',Auth::requireLogin());
             Router::get('/api/court/{id}',  self::class . '@getCourtWithCapacity',Auth::requireLogin() );
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
 public function delete($reservation_id = null) {
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
   public function post(){
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

      public function getCourtWithCapacity($id = null)  {
             $response = new APIResponse();

        if ($id === null ) {
           return $response->setStatusCode(400)->setData(['error' => "L'identifiant court est  manquant ."])->send(); // a titre de log que  vous utiliserez aussi en local pour y  voir le passage  et d'adapter en fonction

    }
        $court = Court::findById($id);  // model , il recup la propriete
             if(!$court){ // ou envoie l'information du non format
                  return $response->setStatusCode(404)->setData(['error' => "Terrain non trouvée !  . id court = " .$id ])->send();
         }

    return  $response->setStatusCode(200)->setData([ 'court' =>   $court   ])->send(); // mise  sous data,  de cette facon car vu notre experience, je me suis focalise et vous devais adapter au test grace aux json qu'on y cible dans le js dans mon approche : si ok les requete ok, si ko le probleme peut se situer au plus proche

 }


 public function teamBooking(){  // meme model que plus haut , tout simplement pour la logique team, ou via default la method de Booking si le id team est absente
      $response = new APIResponse();
    $currentUserId = $_SESSION['user_id'];
        $data = $_POST;
       if (!$currentUserId) {
              return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
           }

      if(isset($data['team_name']) && isset($data['nb_participants'])){ // if a  nouveau element via  html via  js, a gerer par post

                $member_id = $currentUserId;
           $court_id = $data['court_id'];
             $reservation_date = $data['reservation_date'];
            $start_times = $data['start_time'];
          $teamName  =  $data['team_name'];
        $nbParticipants = $data['nb_participants'];
        $team_id =  Team::create(1, $teamName);// creation depuis model et il sera toujours utilisé pour des  requete plus poussée . avec ses insertions ( qu'il y avait de base , tel quel,  vue tous nos  retours afin que cela fonctionne et je me repete)
          $teamParticipant = new TeamParticipant(); // le mode a bien son rôle ici!
           for ($i = 0; $i < $nbParticipants; $i++) { $teamParticipant->addParticipant($team_id,  $member_id); }
     
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
    Booking::addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time, $team_id );
         }
  return $response->setStatusCode(201)->setData(['message' => 'Réservation équipe avec nouveau nom/ nombre ajoutée avec succès!'])->send();

       }
  else if (isset($data['team_id'])){ // en utilisant cette conditions, les requêtes qui appelle au js peuvent  rester "neutres " en attente et un controller prend  la main selon  la direction choisie ( id / type ou autre depuis l'action des inputs en html)
    $teamId =  $data['team_id'];
            $member_id = $currentUserId;
           $court_id = $data['court_id'];
         $reservation_date = $data['reservation_date'];
           $start_times = $data['start_time'];
     if (empty($member_id) || empty($court_id) || empty($reservation_date) || empty($start_times) || empty($teamId)) {
         return $response->setStatusCode(400)->setData(['error' => 'Missing required fields with a specific team . check api. '])->send();
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

   Booking::addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time ,$teamId); // cette valeur est validé et doit donc prendre en consideration
    }
    return $response->setStatusCode(201)->setData(['message' => 'Réservation équipe  par id existant created successfully id='.$teamId])->send();// ici c'est juste , l'implémentation que je demande , je force rien . Afin qu'en js elle doit marcher. Si cette log n'etait pas le cas . a vous de choisir . car au vu du JS (et nos  debug ) les id  passent donc la structue est propre. , j'essais  maintenant , que vous voyez de la meme maniére aussi les logs a ce niveau
      }  else{
  return  $this->post();// methode default en  POST  via le code JS
   }

 }

   }
