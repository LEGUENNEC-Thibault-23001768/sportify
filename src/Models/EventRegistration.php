<?php

namespace Models;

use Core\Database;
use Models\User;
use PDO;

class EventRegistration {
    public static function isUserRegistered($eventId, $userId) {
        $sql = "SELECT * FROM EVENT_REGISTRATION WHERE event_id = :eventId AND member_id = :userId";
        $params = [
            ':eventId' => $eventId,
            ':userId' => $userId
        ];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function registerUserToEvent($eventId, $userId) {
        $sql = "INSERT INTO EVENT_REGISTRATION (event_id, member_id) VALUES (:eventId, :userId)";
        $params = [
            ':eventId' => $eventId,
            ':userId' => $userId
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function getParticipantsByEvent($eventId) {
        $sql = "SELECT * FROM EVENT_REGISTRATION WHERE event_id = :eventId";
        $params = [':eventId' => $eventId];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}