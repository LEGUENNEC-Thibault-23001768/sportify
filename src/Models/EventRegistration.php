<?php

namespace Models;

use Core\Database;
use PDO;

class EventRegistration
{
    /**
     * @param $eventId
     * @param $userId
     * @return mixed
     */
    public static function isUserRegistered($eventId, $userId): mixed
    {
        $sql = "SELECT * FROM EVENT_REGISTRATION WHERE event_id = :eventId AND member_id = :userId";
        $params = [
            ':eventId' => $eventId,
            ':userId' => $userId
        ];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $eventId
     * @param $userId
     * @return bool
     */
    public static function registerUserToEvent($eventId, $userId): bool
    {
        $sql = "INSERT INTO EVENT_REGISTRATION (event_id, member_id) VALUES (:eventId, :userId)";
        $params = [
            ':eventId' => $eventId,
            ':userId' => $userId
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $eventId
     * @param $userId
     * @return bool
     */
    public static function unregisterUserFromEvent($eventId, $userId): bool
    {
        $sql = "DELETE FROM EVENT_REGISTRATION WHERE event_id = :eventId AND member_id = :userId";
        $params = [
            ':eventId' => $eventId,
            ':userId' => $userId
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $eventId
     * @return array
     */
    public static function getParticipantsByEvent($eventId): array
    {
        $sql = "SELECT * FROM EVENT_REGISTRATION WHERE event_id = :eventId";
        $params = [':eventId' => $eventId];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}