<?php

namespace Models;

use Core\Database;
use PDO;

class EventRegistration {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }


    public function isUserRegistered($eventId, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM EVENT_REGISTRATION WHERE event_id = ? AND member_id = ?");
        $stmt->execute([$eventId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registerUserToEvent($eventId, $userId) {
        $stmt = $this->db->prepare("INSERT INTO EVENT_REGISTRATION (event_id, member_id) VALUES (?, ?)");
        return $stmt->execute([$eventId, $userId]);
    }

    public function getParticipantsByEvent($eventId) {
        $stmt = $this->db->prepare("SELECT * FROM EVENT_REGISTRATION WHERE event_id = ?");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
    


