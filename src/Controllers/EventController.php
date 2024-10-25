<?php

namespace Controllers;

use Core\View;
use Models\Event;
use Models\Team;
use Models\User;
use Models\EventRegistration;

class EventController
{
    public function __construct()
    {
        $this->view = new View();
    }

    public function index()
    {
        $currentUserId = $_SESSION['user_id'];
        $eventModel = new Event();
        $memberModel = new User();
        $member = $memberModel->find($currentUserId);


        $events = $eventModel->getAllEvents();

        
        echo $this->view->render('/dashboard/events/index', ['events' => $events, 'member' => $member]);
    }


    public function create()
    {
        $currentUserId = $_SESSION['user_id'];
        $memberModel = new User();
        $currentUser = $memberModel->find($currentUserId);
        


        if ($currentUser['status'] !== 'coach' && $currentUser['status'] !== 'admin') {
            echo $this->view->render('/dashboard/events/index', ['error' => 'You are not authorized to create events']);
            return;
        }

            echo $this->view->render('/dashboard/events/create');
    }

    public function store()
    {
        $currentUserId = $_SESSION['user_id'];
        $memberModel = new User();
        $currentUser = $memberModel->find($currentUserId);


        if ($currentUser['status'] !== 'coach' && $currentUser['status'] !== 'admin') {
            echo $this->view->render('/dashboard/events/index', ['error' => 'You are not authorized to create events']);
            return;
        }

        $eventData = $_POST;

        if (empty($eventData['event_name']) || empty($eventData['event_date']) || empty($eventData['start_time']) || empty($eventData['end_time']) || empty($eventData['max_participants']) || empty($eventData['location'])) {
            echo $this->view->render('/dashboard/events/create', ['error' => 'All required fields must be filled']);
            return;
        }

        $eventModel = new Event();
        $eventModel->createEvent($eventData);

        header('Location: /dashboard/events');
        exit;
    }
    

    public function join($eventId) {
        $eventModel = new Event();
        $eventRegistrationModel = new EventRegistration();
        
        $currentUserId = $_SESSION['user_id'];
        
        $event = $eventModel->find($eventId);
        
        if (!$event) {
            echo $this->view->render('/dashboard/events/index', ['error' => 'Event not found']);
            return;
        }
    
        if ($eventRegistrationModel->isUserRegistered($eventId, $currentUserId)) {
            echo $this->view->render('/dashboard/events/index', ['error' => 'You are already registered for this event']);
            return;
        }
    
        if ($event['participants_count'] >= $event['max_participants']) {
            echo $this->view->render('/dashboard/events/index', ['error' => 'The event is already full']);
            return;
        }
    
        $eventRegistrationModel->registerUserToEvent($eventId, $currentUserId);
        
        header('Location: /dashboard/events');
        exit;
    }
    


    public function delete($eventId) {
        $eventModel = new Event();
        $currentUserId = $_SESSION['user_id'];
        $memberModel = new User();
        $member = $memberModel->find($currentUserId);
    
        $event = $eventModel->find($eventId);
    
        if (!$event) {
            echo "1";
            return $this->view->render('/dashboard/events/index', ['error' => 'Event not found']);
        }
        //                                                                                                    
        if ($event['created_by'] != $currentUserId && $member['status'] !== "coach" && $member['status'] !== "admin") {
            echo "2";

            return $this->view->render('/dashboard/events/index', ['error' => 'You are not authorized to delete this event']);
        }
    
        if ($eventModel->deleteEvent($eventId)) {
            echo "3";

            header('Location: /dashboard/events');
        } else {
            return $this->view->render('/dashboard/events/index', ['error' => 'Failed to delete event']);
        }
    }
    
    


}


?>