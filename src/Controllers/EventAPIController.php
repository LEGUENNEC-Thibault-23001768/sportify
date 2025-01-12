<?php

namespace Controllers;

use Core\Config;
use Core\APIResponse;
use Core\APIController;
use Core\Router;
use Core\RouteProvider;
use Models\Event;
use Models\User;
use Models\EventRegistration;
use Models\EventInvitation;
use Models\Booking;
use DateTime;
use Core\Auth;

class EventAPIController extends APIController implements RouteProvider
{
    public static function routes(): void
    {
        Router::get('/api/events', self::class . '@getEvents', Auth::requireLogin());
        Router::get('/api/events/{id}', self::class . '@show', Auth::requireLogin());
        Router::post('/api/events', self::class . '@storeApi', [Auth::isAdmin()]); // Auth::isCoach()
        Router::post('/api/events/join/{id}', self::class . '@postJoin', Auth::requireLogin());
        Router::post('/api/events/leave/{id}', self::class . '@postLeave', Auth::requireLogin());
        Router::delete('/api/events/{id}', self::class . '@deleteApi', [Auth::isAdmin(), Auth::isCoach()]);
        Router::post('/api/events/{id}/invite', self::class . '@postSendInviteApi', [Auth::isAdmin(), Auth::isCoach()]);
    }


    public function getEvents()
    {
        return $this->handleRequest($_SERVER['REQUEST_METHOD']);
    }
    public function show($eventId)
    {
        return $this->handleRequest($_SERVER['REQUEST_METHOD'], $eventId);
    }

    public function get($eventId = null)
    {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
    
        if ($eventId === null) {
            $events = Event::getAllEvents();
            $mobiscrollEvents = [];
            foreach ($events as $event) {
                $participants = $this->getEventParticipants($event, $member, $currentUserId);
    
                $mobiscrollEvents[] = [
                    'id' => $event['event_id'],
                    'title' => $event['event_name'],
                    'start' => $event['event_date'] . 'T' . $event['start_time'],
                    'end' => $event['event_date'] . 'T' . $event['end_time'],
                    'description' => $event['description'],
                    'location' => $event['location'],
                    'max_participants' => $event['max_participants'],
                    'created_by' => $event['created_by'],
                    'is_registered' => EventRegistration::isUserRegistered($event['event_id'], $currentUserId),
                    'participants' => $participants
                ];
            }
        
            return (new APIResponse())->setStatusCode(200)->setData($mobiscrollEvents)->send();
        } else {
            $event = Event::findEvents($eventId);
            if (!$event) {
                return (new APIResponse())->setStatusCode(404)->setData(['error' => 'Event not found'])->send();
            }
    
            $isRegistered = EventRegistration::isUserRegistered($eventId, $currentUserId);
            $participants = $this->getEventParticipants($event, $member, $currentUserId, true);
    
            $eventData = [
                'id' => $event['event_id'],
                'event_name' => $event['event_name'],
                'event_date' => $event['event_date'],
                'start_time' => $event['start_time'],
                'end_time' => $event['end_time'],
                'description' => $event['description'],
                'location' => $event['location'],
                'max_participants' => $event['max_participants'],
                'created_by' => $event['created_by'],
                'participants' => $participants,
                'is_registered' => $isRegistered
            ];
            return (new APIResponse())->setStatusCode(200)->setData($eventData)->send();
        }
    }

     private function getEventParticipants($event, $member, $currentUserId, $isSingleEvent = false)
    {
         $participants = [];
         $canViewParticipants = $member['status'] === 'coach' || $member['status'] === 'admin' || $event['created_by'] == $currentUserId;
         if ($canViewParticipants) {
            $registrations = EventRegistration::getParticipantsByEvent($isSingleEvent ? $event['event_id'] : $event['event_id'] );
             foreach ($registrations as $registration) {
                 $participants[] = User::getUserById($registration['member_id']);
             }
        }
         return $participants;
    }
    public function storeApi()
    {
        return $this->handleRequest($_SERVER['REQUEST_METHOD']);
    }

    public function post()
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $currentUser = User::getUserById($currentUserId);
        if ($currentUser['status'] !== 'coach' && $currentUser['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'Unauthorized'])->send();
        }

        $eventData = $_POST;
        if (
            empty($eventData['event_name']) ||
            empty($eventData['max_participants']) || empty($eventData['location'])
        ) {
            return $response->setStatusCode(400)->setData(['error' => 'Missing required fields'])->send();
        }

        $booking = $this->findBooking($eventData['location'], $eventData['event_date'] ?? date('Y-m-d'));
        if (!$booking) {
           return $response->setStatusCode(500)->setData(['error' => 'Location was not found'])->send();
        }
         if ($booking['event_id'] != null) {
             return $response->setStatusCode(500)->setData(['error' => 'Location is already booked'])->send();
         }

         if ($booking['start_time'] < '08:00' || $booking['end_time'] > '22:00') {
             return $response->setStatusCode(500)->setData(['error' => 'Event is starting too early or ending too late'])->send();
         }

         $eventData['event_date'] = $booking['reservation_date'];
         $eventData['start_time'] = $booking['start_time'];
         $eventData['end_time'] = $booking['end_time'];


         if (!is_numeric($eventData['max_participants']) || $eventData['max_participants'] > 100) {
             return $response->setStatusCode(400)->setData(['error' => 'Maximum participants can\'t be over 100.'])->send();
         }
        $eventData['created_by'] = $currentUserId;

         $eventId = Event::createEvent($eventData);
         if (!empty($eventData['invitations'])) {
             $emails = explode(',', $eventData['invitations']);
            $emails = array_map('trim', $emails);
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $token = EventInvitation::createInvitation($eventId, $email);
                    $eventData['event_id'] = $eventId;
                    $this->sendInvitationEmail($email, $token, $eventData);
                }
            }
        }


        if (!$eventId) {
            return $response->setStatusCode(500)->setData(['error' => 'Failed to create event'])->send();
        }

        Booking::updateReservation($booking['reservation_id'], $booking['reservation_date'], $booking['start_time'], $booking['end_time'], $eventId);

        $mobiscrollEvent = [
            'id' => $eventId,
            'title' => $eventData['event_name'],
            'start' => $eventData['event_date'] . 'T' . $eventData['start_time'],
            'end' => $eventData['event_date'] . 'T' . $eventData['end_time'],
            'description' => $eventData['description'],
            'location' => $eventData['location'],
            'max_participants' => $eventData['max_participants'],
            'created_by' => $eventData['created_by'],
            'is_registered' => false,
        ];
        return $response->setStatusCode(201)->setData($mobiscrollEvent)->send();
    }


   private function findBooking($location, $date) {
       $reservations = Booking::getAllReservations();
        foreach ($reservations as $reservation) {
            if ($reservation['court_name'] == $location && $reservation['reservation_date'] == $date) {
                 return $reservation;
            }
        }
        return null;
   }


    public function deleteApi($eventId)
    {
       return $this->handleRequest($_SERVER['REQUEST_METHOD'], $eventId);
    }

  public function delete($eventId = null)
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $currentUser = User::getUserById($currentUserId);
        $event = Event::findEvents($eventId);

        if ($currentUser['status'] !== 'coach' && $currentUser['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'Unauthorized'])->send();
        }

          if (!$event) {
              $response->setStatusCode(404)->setData(['error' => 'Event not found'])->send();
                return;
           }

        $booking = $this->findBooking($event['location'], $event['event_date']);

        if (isset($booking)) {
            if ($booking['event_id'] == null) return null;

            if ($booking['event_id'] == $eventId) {
                Booking::updateReservation($booking['reservation_id'], $booking['reservation_date'], $booking['start_time'], $booking['end_time'], null);
            }
        }

        if (Event::deleteEvent($eventId)) {
               $response->setData(['message' => 'Event deleted successfully'])->send();
           } else {
                $response->setStatusCode(500)->setData(['error' => 'Failed to delete event'])->send();
          }
    }

    public function join($eventId) {
       return $this->handleRequest($_SERVER['REQUEST_METHOD'], $eventId);
    }

     public function leave($eventId) {
         return $this->handleRequest($_SERVER['REQUEST_METHOD'], $eventId);
     }
    public function postJoin($eventId) {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $event = Event::findEvents($eventId);

        if (!$event) {
            return $response->setStatusCode(404)->setData(['error' => 'Event not found'])->send();
        }

       if (EventRegistration::isUserRegistered($eventId, $currentUserId)) {
           return $response->setStatusCode(400)->setData(['error' => 'You are already registered for this event'])->send();
       }
        $updatedEvent = Event::findEvents($eventId);
          $participantsCount = 0;
        if (isset($event['participants'])) $participantsCount = count($event['participants']);

        if ($participantsCount >= $updatedEvent['max_participants']) {
         return $response->setStatusCode(400)->setData(['error' => 'The event is already full'])->send();
        }

        EventRegistration::registerUserToEvent($eventId, $currentUserId);
         $updatedEvent['participants'] = $this->getEventParticipants($updatedEvent, User::getUserById($currentUserId), $currentUserId, true);

        $updatedEventData = [
            'id' => $updatedEvent['event_id'],
            'title' => $updatedEvent['event_name'],
            'start' => $updatedEvent['event_date'] . 'T' . $updatedEvent['start_time'],
            'end' => $updatedEvent['event_date'] . 'T' . $updatedEvent['end_time'],
            'description' => $updatedEvent['description'],
            'location' => $updatedEvent['location'],
            'max_participants' => $updatedEvent['max_participants'],
            'created_by' => $updatedEvent['created_by'],
           'participants' => $updatedEvent['participants'],
            'is_registered' => true
        ];
     
          return $response->setStatusCode(200)->setData(['message' => 'Successfully joined the event', 'event' => $updatedEventData])->send();
   }
   public function postLeave($eventId) {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $event = Event::findEvents($eventId);

        if (!$event) {
            return $response->setStatusCode(404)->setData(['error' => 'Event not found'])->send();
        }
        if (!EventRegistration::isUserRegistered($eventId, $currentUserId)) {
            return $response->setStatusCode(400)->setData(['error' => 'You are not registered for this event'])->send();
        }

        EventRegistration::unregisterUserFromEvent($eventId, $currentUserId);

        $updatedEvent = Event::findEvents($eventId);
        $updatedEvent['participants'] = $this->getEventParticipants($updatedEvent,  User::getUserById($currentUserId), $currentUserId, true);

        $updatedEventData = [
            'id' => $updatedEvent['event_id'],
            'title' => $updatedEvent['event_name'],
            'start' => $updatedEvent['event_date'] . 'T' . $updatedEvent['start_time'],
            'end' => $updatedEvent['event_date'] . 'T' . $updatedEvent['end_time'],
            'description' => $updatedEvent['description'],
            'location' => $updatedEvent['location'],
            'max_participants' => $updatedEvent['max_participants'],
            'created_by' => $updatedEvent['created_by'],
            'participants' => $updatedEvent['participants'],
            'is_registered' => false
        ];
        return $response->setStatusCode(200)->setData(['message' => 'Successfully left the event', 'event' => $updatedEventData])->send();
    }


    public function postSendInviteApi($eventId) {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $currentUser = User::getUserById($currentUserId);
        $event = Event::findEvents($eventId);
        if (!$event) {
            $response->setStatusCode(404)->setData(['error' => 'Event not found'])->send();
            return;
        }

        // Check if the user is authorized to send invitations
        if ($event['created_by'] != $currentUserId && $currentUser['status'] !== "coach" && $currentUser['status'] !== "admin") {
                $response->setStatusCode(403)->setData(['error' => 'You are not authorized to send invitations'])->send();
            return;
        }
        $email = $_POST['email'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->setStatusCode(400)->setData(['error' => 'Invalid email format'])->send();
        return;
        }

        $user = User::getUserByEmail($email);
        if ($user && EventRegistration::isUserRegistered($eventId, $user['member_id'])) {
            $response->setStatusCode(400)->setData(['error' => 'User is already registered for this event'])->send();
        return;
        }

        $existingInvitation = EventInvitation::findInvitationsByEventIdAndEmail($eventId, $email);
        if ($existingInvitation) {
                $response->setStatusCode(400)->setData(['error' => 'User is already invited to this event'])->send();
            return;
            }

        $token = EventInvitation::createInvitation($eventId, $email);
        if ($this->sendInvitationEmail($email, $token, $event)) {
            $response->setStatusCode(200)->setData(['message' => 'Invitation sent successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to send invitation'])->send();
        }
    }

    private function sendInvitationEmail($email, $token, $event) {
        $mail_parts = Config::get("mail_parts");
        $invitationLink = Config::get("server_url") . "/event/invitation/" . $token;
        $eventName = $event['event_name'];

        $title = "Invitation to " . $eventName;
        $mail_parts['mail_body'] = str_replace("[TITLE]", $title, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[PARAGRAPH]", "You have been invited to the following event: " . $eventName, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[VERIFY_URL]", $invitationLink, $mail_parts['mail_body']);
        $mail_parts['mail_body'] = str_replace("[ANCHOR]", "Accept Invitation", $mail_parts['mail_body']);

        $subject = $title;
        $message = $mail_parts['mail_head'] .
        $mail_parts['mail_title'] .
        $mail_parts['mail_head_end'] .
        $mail_parts['mail_body'] .
        $mail_parts['mail_footer'];
        $headers = "From: sportify@alwaysdata.net\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($email, $subject, $message, $headers)) {
            return true;
        } else {
            error_log("Error sending invitation email to $email: " . error_get_last());
            return false;
        }
    }
}