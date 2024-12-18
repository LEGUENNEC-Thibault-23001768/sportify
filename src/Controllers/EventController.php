<?php

namespace Controllers;

use Core\APIResponse;
use Core\Config;
use Core\View;
use Models\Event;
use Models\EventInvitation;
use Models\EventRegistration;
use Models\User;

class EventController
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); // Redirect to login if not logged in
            exit;
        }

        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);

        echo View::render('/dashboard/events/index', [
            'member' => $member,
            'currentUserId' => $currentUserId
        ]);
    }

    public function getEvents()
    {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
        $events = Event::getAllEvents();

        $mobiscrollEvents = [];
        foreach ($events as $event) {
            $participants = [];
            // Check if the user is authorized to view participants
            if ($member["status"] === 'coach' || $member['status'] === 'admin' || $event['created_by'] == $currentUserId) {
                $registrations = EventRegistration::getParticipantsByEvent($event['event_id']);
                foreach ($registrations as $registration) {
                    $participants[] = User::getUserById($registration['member_id']);
                }
            }


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

        $response = new APIResponse();
        $response->setStatusCode(200)->setData($mobiscrollEvents)->send();
    }


    /**
     * @throws \DateMalformedStringException
     */
    public function storeApi()
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $currentUser = User::getUserById($currentUserId);

        if ($currentUser['status'] !== 'coach' && $currentUser['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'Unauthorized'])->send();
        }

        $eventData = $_POST;

        if (empty($eventData['event_name']) || empty($eventData['event_date']) || empty($eventData['start_time']) || empty($eventData['end_time']) || empty($eventData['max_participants']) || empty($eventData['location'])) {
            return $response->setStatusCode(400)->setData(['error' => 'Missing required fields'])->send();
        }

        $start = new \DateTime($eventData['event_date'] . 'T' . $eventData['start_time']);
        $end = new \DateTime($eventData['event_date'] . 'T' . $eventData['end_time']);
        $duration = $start->diff($end);

        if ($duration->h > 2) {
            return $response->setStatusCode(400)->setData(['error' => 'Event duration cannot exceed 2 hours'])->send();
        }

        if (!is_numeric($eventData['max_participants']) || $eventData['max_participants'] < 5) {
            return $response->setStatusCode(400)->setData(['error' => 'Maximum participants must be a number and at least 5'])->send();
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

        if (!isset($eventData['participants'])) {
            $eventData['participants'] = [];
        }

        if (!$eventId) {
            return $response->setStatusCode(500)->setData(['error' => 'Failed to create event'])->send();
        }

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
            'participants' => $eventData['participants'] || [],
        ];

        return $response->setStatusCode(201)->setData($mobiscrollEvent)->send();
    }

    private function sendInvitationEmail($email, $token, $event)
    {
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

    public function sendInviteApi($eventId)
    {
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

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->setStatusCode(400)->setData(['error' => 'Invalid email format'])->send();
            return;
        }

        // Check if the user is already registered or invited
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

        // Send invitation email
        if ($this->sendInvitationEmail($email, $token, $event)) {
            $response->setStatusCode(200)->setData(['message' => 'Invitation sent successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to send invitation'])->send();
        }
    }

    public function deleteApi($eventId)
    {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $currentUser = User::getUserById($currentUserId);
        $event = Event::findEvents($eventId);

        if ($currentUser['status'] !== 'coach' && $currentUser['status'] !== 'admin') {
            $response->setStatusCode(403)->setData(['error' => 'Unauthorized'])->send();
            return;
        }

        if (!$event) {
            $response->setStatusCode(404)->setData(['error' => 'Event not found'])->send();
            return;
        }

        if (Event::deleteEvent($eventId)) {
            $response->setData(['message' => 'Event deleted successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to delete event'])->send();
        }
    }

    public function show($eventId)
    {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
        $event = Event::findEvents($eventId);

        if (!$event) {
            return (new APIResponse)->setStatusCode(404)->setData(['error' => 'Event not found'])->send();
        }

        $isRegistered = EventRegistration::isUserRegistered($eventId, $currentUserId);
        $canViewParticipants = ($member['status'] === 'coach' || $member['status'] === 'admin' || $event['created_by'] == $currentUserId);

        $participants = [];
        if ($canViewParticipants) {
            $registrations = EventRegistration::getParticipantsByEvent($eventId);
            foreach ($registrations as $registration) {
                $participants[] = User::getUserById($registration['member_id']);
            }
        }

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
            'participants' => $canViewParticipants ? $participants : [],
            'is_registered' => $isRegistered
        ];

        $response = new APIResponse();
        $response->setStatusCode(200)->setData($eventData)->send();
    }

    public function join($eventId)
    {
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
//        if ($event['participants_count'] >= $event['max_participants']) {
            return $response->setStatusCode(400)->setData(['error' => 'The event is already full'])->send();
        }

        EventRegistration::registerUserToEvent($eventId, $currentUserId);
        $registrations = EventRegistration::getParticipantsByEvent($eventId);
        foreach ($registrations as $registration) {
            $updatedEvent['participants'][] = User::getUserById($registration['member_id']);
        }

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

    public function leave($eventId)
    {
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
        $updatedEventData = [
            'id' => $updatedEvent['event_id'],
            'title' => $updatedEvent['event_name'],
            'start' => $updatedEvent['event_date'] . 'T' . $updatedEvent['start_time'],
            'end' => $updatedEvent['event_date'] . 'T' . $updatedEvent['end_time'],
            'description' => $updatedEvent['description'],
            'location' => $updatedEvent['location'],
            'max_participants' => $updatedEvent['max_participants'],
            'created_by' => $updatedEvent['created_by'],
            'participants' => EventRegistration::getParticipantsByEvent($eventId),
            'is_registered' => false
        ];

        return $response->setStatusCode(200)->setData(['message' => 'Successfully left the event', 'event' => $updatedEventData])->send();
    }
}