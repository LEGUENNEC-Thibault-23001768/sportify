<?php

namespace Models;

use Core\Database;
use PDO;

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }


    public function getAllEvents() {
        $stmt = $this->db->query("
            SELECT 
                e.event_id, 
                e.event_name, 
                e.event_date, 
                e.location, 
                e.max_participants, 
                COUNT(er.member_id) AS participants_count,
                m.status AS status,
                m.last_name AS created_by_name,
                e.created_by
            FROM EVENTS e
            LEFT JOIN EVENT_REGISTRATION er ON e.event_id = er.event_id 
            JOIN MEMBER m ON e.created_by = m.member_id
            GROUP BY e.event_id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    

    public function find($eventId) {
        $stmt = $this->db->prepare("SELECT * FROM EVENTS WHERE event_id = ?");
        $stmt->execute([$eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    

    public function createEvent($data) {
        $stmt = $this->db->prepare("INSERT INTO EVENTS (event_name, event_date, start_time, end_time, description, max_participants, location, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $currentUserId = $_SESSION['user_id'];
        
        return $stmt->execute([
            $data['event_name'], 
            $data['event_date'], 
            $data['start_time'], 
            $data['end_time'], 
            $data['description'], 
            $data['max_participants'],
            $data['location'],
            $currentUserId
        ]);
    }

    public function deleteEvent($eventId) {
        $stmt = $this->db->prepare("DELETE FROM EVENTS WHERE event_id = ?");
        return $stmt->execute([$eventId]);
    }
}
