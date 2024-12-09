<?php

namespace Controllers;

use Core\View;
use Core\Config;
use Models\Event;
use Models\User;
use Models\EventRegistration;
use Models\EventInvitation;

class EventController
{
    public function index()
    {
        $currentUserId = $_SESSION['user_id'];
        
        $member = User::getUserById($currentUserId);
        $events = Event::getAllEvents();
        
        echo View::render('/dashboard/events/index', ['events' => $events, 'member' => $member]);
    }

    public function create()
    {
        $currentUserId = $_SESSION['user_id'];
        $currentUser = User::getUserById($currentUserId);

        if ($currentUser['status'] !== 'coach' && $currentUser['status'] !== 'admin') {
            echo View::render('/dashboard/events/index', ['error' => 'You are not authorized to create events']);
            return;
        }

        echo View::render('/dashboard/events/create');
    }

    public function store()
    {
        $currentUserId = $_SESSION['user_id'];
        $currentUser = User::getUserById($currentUserId);

        if ($currentUser['status'] !== 'coach' && $currentUser['status'] !== 'admin') {
            echo View::render('/dashboard/events/index', ['error' => 'You are not authorized to create events']);
            return;
        }

        $eventData = $_POST;

        if (empty($eventData['event_name']) || empty($eventData['event_date']) || empty($eventData['start_time']) || empty($eventData['end_time']) || empty($eventData['max_participants']) || empty($eventData['location'])) {
            echo View::render('/dashboard/events/create', ['error' => 'All required fields must be filled']);
            return;
        }

        Event::createEvent($eventData);

        header('Location: /dashboard/events');
        exit;
    }

    public function join($eventId) {
        
        $currentUserId = $_SESSION['user_id'];
        
        $event = Event::findEvents($eventId);
        
        if (!$event) {
            echo View::render('/dashboard/events/index', ['error' => 'Event not found']);
            return;
        }
    
        if (EventRegistration::isUserRegistered($eventId, $currentUserId)) {
            echo View::render('/dashboard/events/index', ['error' => 'You are already registered for this event']);
            return;
        }
    
        if ($event['participants_count'] >= $event['max_participants']) {
            echo View::render('/dashboard/events/index', ['error' => 'The event is already full']);
            return;
        }
    
        EventRegistration::registerUserToEvent($eventId, $currentUserId);
        
        header('Location: /dashboard/events');
        exit;
    }

    public function delete($eventId) {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
    
        $event = Event::findEvents($eventId);
    
        if (!$event) {
            echo View::render('/dashboard/events/index', ['error' => 'Event not found']);
        }
        if ($event['created_by'] != $currentUserId && $member['status'] !== "coach" && $member['status'] !== "admin") {
            echo View::render('/dashboard/events/index', ['error' => 'You are not authorized to delete this event']);
        }
    
        if (Event::deleteEvent($eventId)) {
            header('Location: /dashboard/events');
        } else {
            echo View::render('/dashboard/events/index', ['error' => 'Failed to delete event']);
        }
    }


    public function invite($eventId)
    {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
        $event = Event::findEvents($eventId);

        if (!$event) {
            echo View::render('/dashboard/events/index', ['error' => 'Event not found']);
            return;
        }


        // Check if the current user is authorized to invite
        if ($event['created_by'] != $currentUserId && $member['status'] !== "coach" && $member['status'] !== "admin") {
            echo View::render('/dashboard/events/index', ['error' => 'You are not authorized to invite users to this event']);
            return;
        }
        $invitations = EventInvitation::findInvitationsByEventId($eventId);

        echo View::render('/dashboard/events/invite', ['event' => $event, 'invitations' => $invitations]);
    }

    public function sendInvite($eventId)
    {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
        $event = Event::findEvents($eventId);

        if (!$event) {
            echo View::render('/dashboard/events/index', ['error' => 'Event not found']);
            return;
        }

        if ($event['created_by'] != $currentUserId && $member['status'] !== "coach" && $member['status'] !== "admin") {
            echo View::render('/dashboard/events/index', ['error' => 'You are not authorized to invite users to this event']);
            return;
        }

        $email = $_POST['email'];

        // Validate email (you might want to add more robust validation)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo View::render('/dashboard/events/invite', ['event' => $event, 'error' => 'Invalid email format']);
            return;
        }

        // Check if the user is already registered or invited
        if (EventRegistration::isUserRegistered($eventId, User::getUserByEmail($email)['member_id'])) {
            echo View::render('/dashboard/events/invite', ['event' => $event, 'error' => 'User is already registered for this event']);
            return;
        }

        $invitation = EventInvitation::findInvitationsByEventIdAndEmail($eventId, $email);

        if ($invitation) {
            echo View::render('/dashboard/events/invite', ['event' => $event, 'error' => 'User is already invited to this event']);
            return;
        }

        // Create invitation
        $token = EventInvitation::createInvitation($eventId, $email);

        // Send invitation email
        if ($this->sendInvitationEmail($email, $token, $event)) {
            echo View::render('/dashboard/events/invite', ['event' => $event, 'success' => 'Invitation sent successfully!']);
        } else {
            $invitation = EventInvitation::findInvitationByToken($token);
            if ($invitation) {
                EventInvitation::deleteInvitation($invitation['invitation_id']);
            }
    
            echo View::render('/dashboard/events/invite', ['event' => $event, 'error' => 'Failed to send invitation email. Please try again.']);
        }
    }

    public function show($eventId)
    {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
        $event = Event::findEvents($eventId);

        if (!$event) {
            echo View::render('/dashboard/events/index', ['error' => 'Event not found']);
        }

        // Check if the user has permission to view the event details (event creator, coach, admin, or registered user)
        $isRegistered = EventRegistration::isUserRegistered($eventId, $currentUserId);
        if ($event['created_by'] != $currentUserId && $member['status'] !== "coach" && $member['status'] !== "admin" && !$isRegistered) {
            echo View::render('/dashboard/events/index', ['error' => 'You are not authorized to view this event']);
        }

        // Get registered users (participants)
        $registrations = EventRegistration::getParticipantsByEvent($eventId);
        $participants = [];
        foreach ($registrations as $registration) {
            $participants[] = User::getUserById($registration['member_id']);
        }

        // Get invited users
        $invitations = EventInvitation::findInvitationsByEventId($eventId);

        echo View::render('/dashboard/events/show', [
            'event' => $event,
            'member' => $member,
            'participants' => $participants,
            'invitations' => $invitations
        ]);
    }

    public function acceptInvite($token)
    {
        $invitation = EventInvitation::findInvitationByToken($token);

        if (!$invitation) {
            return View::render('/dashboard/events/index', ['error' => 'Invalid invitation token.']);
        }

        $eventId = $invitation['event_id'];
        $event = Event::findEvents($eventId);

        if (!$event) {
            return View::render('/dashboard/events/index', ['error' => 'Event not found.']);
        }

        // Check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Store invitation token in session to use after login/registration
            $_SESSION['invitation_token'] = $token;
            // Redirect to login page with a message
            return View::render('/auth/login', ['message' => 'Please login to accept the invitation.']);
        }

        $userId = $_SESSION['user_id'];

        // Check if already registered
        if (EventRegistration::isUserRegistered($eventId, $userId)) {
            EventInvitation::deleteInvitation($invitation['invitation_id']);
            return View::render('/dashboard/events/index', ['error' => 'You are already registered for this event.']);
        }

        // Register user for the event
        EventRegistration::registerUserToEvent($eventId, $userId);

        // Delete invitation
        EventInvitation::deleteInvitation($invitation['invitation_id']);

        // Remove invitation token from session if it exists
        unset($_SESSION['invitation_token']);

        return View::render('/dashboard/events/index', ['success' => 'You have successfully joined the event!']);
    }

    public function deleteInvitation($invitationId) {
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
    
        $invitation = EventInvitation::findInvitationById($invitationId);
    
        if (!$invitation) {
            return View::render('/dashboard/events/index', ['error' => 'Invitation not found']);
        }
    
        $event = Event::findEvents($invitation['event_id']);
    
        if (!$event) {
            return View::render('/dashboard/events/index', ['error' => 'Event not found']);
        }
    
        if ($event['created_by'] != $currentUserId && $member['status'] !== "coach" && $member['status'] !== "admin") {
            return View::render('/dashboard/events/index', ['error' => 'You are not authorized to delete this invitation']);
        }
    
        if (EventInvitation::deleteInvitation($invitationId)) {
            // Redirect to the event's invite page with a success message
            return View::render('/dashboard/events/invite/' . $event['event_id'],  ['event' => $event, 'success' => 'Invitation deleted successfully!']);
        } else {
            // Redirect with an error message if deletion fails
            return View::render('/dashboard/events/invite/' . $event['event_id'],  ['event' => $event, 'error' => 'Failed to delete invitation']);
        }
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

}