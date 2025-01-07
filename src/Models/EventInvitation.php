<?php

namespace Models;

use Core\Database;
use PDO;
use Random\RandomException;

class EventInvitation
{
    /**
     * @param $eventId
     * @param $email
     * @return string
     * @throws RandomException
     */
    public static function createInvitation($eventId, $email): string
    {
        $token = bin2hex(random_bytes(32)); // Generate a unique token
        $sql = "INSERT INTO EVENT_INVITATIONS (event_id, email, token) VALUES (:event_id, :email, :token)";
        $params = [
            ':event_id' => $eventId,
            ':email' => $email,
            ':token' => $token
        ];
        Database::query($sql, $params);
        return $token;
    }

    /**
     * @param $token
     * @return mixed
     */
    public static function findInvitationByToken($token): mixed
    {
        $sql = "SELECT * FROM EVENT_INVITATIONS WHERE token = :token";
        $params = [':token' => $token];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $eventId
     * @return array
     */
    public static function findInvitationsByEventId($eventId): array
    {
        $sql = "SELECT * FROM EVENT_INVITATIONS WHERE event_id = :event_id";
        $params = [':event_id' => $eventId];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $invitationId
     * @return mixed
     */
    public static function findInvitationById($invitationId): mixed
    {
        $sql = "SELECT * FROM EVENT_INVITATIONS WHERE invitation_id = :invitation_id";
        $params = [':invitation_id' => $invitationId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * @param $invitationId
     * @return bool
     */
    public static function deleteInvitation($invitationId): bool
    {
        $sql = "DELETE FROM EVENT_INVITATIONS WHERE invitation_id = :invitation_id";
        $params = [':invitation_id' => $invitationId];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    /**
     * @param $eventId
     * @param $email
     * @return mixed
     */
    public static function findInvitationsByEventIdAndEmail($eventId, $email): mixed
    {
        $sql = "SELECT * FROM EVENT_INVITATIONS WHERE event_id = :event_id AND email = :email";
        $params = [
            ':event_id' => $eventId,
            ':email' => $email
        ];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

}