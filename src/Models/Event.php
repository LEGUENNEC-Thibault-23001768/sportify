<?php

namespace Models;

use Core\Database;
use PDO;

class Event
{
    /**
     * @return array
     */
    public static function getAllEvents(): array
    {
        $sql = "
            SELECT 
                e.event_id, 
                e.event_name, 
                e.event_date, 
                e.start_time,
                e.end_time,
                e.location, 
                e.max_participants,
                e.description,
                COUNT(er.member_id) AS participants_count,
                m.status AS status,
                m.last_name AS created_by_name,
                e.created_by
            FROM EVENTS e
            LEFT JOIN EVENT_REGISTRATION er ON e.event_id = er.event_id 
            JOIN MEMBER m ON e.created_by = m.member_id
            GROUP BY e.event_id
        ";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $eventId
     * @return mixed
     */
    public static function findEvents($eventId): mixed
    {
        $sql = "SELECT * FROM EVENTS WHERE event_id = :eventId";
        $params = [':eventId' => $eventId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $data
     * @return false|string
     */
    public static function createEvent($data): false|string
    {
        $sql = "INSERT INTO EVENTS (event_name, event_date, start_time, end_time, description, max_participants, location, created_by) 
                VALUES (:event_name, :event_date, :start_time, :end_time, :description, :max_participants, :location, :created_by)";

        $currentUserId = $_SESSION['user_id'];

        $params = [
            ':event_name' => $data['event_name'],
            ':event_date' => $data['event_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':description' => $data['description'],
            ':max_participants' => $data['max_participants'],
            ':location' => $data['location'],
            ':created_by' => $currentUserId
        ];

        $db_resp = Database::query($sql, $params);
        if ($db_resp->rowCount() > 0) {
            return Database::getConnection()->lastInsertId();
        }

        return false;
    }

    /**
     * @param $eventId
     * @return bool
     */
    public static function deleteEvent($eventId): bool
    {
        $sql = "DELETE FROM EVENTS WHERE event_id = :eventId";
        $params = [':eventId' => $eventId];
        return Database::query($sql, $params)->rowCount() > 0;
    }
}