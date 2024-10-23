<?php

namespace Controllers;

use Core\View;
use Models\Event;
use Models\User;
use Models\EventRegistration;

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
            return View::render('/dashboard/events/index', ['error' => 'Event not found']);
        }
    
        if ($event['created_by'] != $currentUserId && $member['status'] !== "coach" && $member['status'] !== "admin") {
            return View::render('/dashboard/events/index', ['error' => 'You are not authorized to delete this event']);
        }
    
        if (Event::deleteEvent($eventId)) {
            header('Location: /dashboard/events');
        } else {
            return View::render('/dashboard/events/index', ['error' => 'Failed to delete event']);
        }
    }
}