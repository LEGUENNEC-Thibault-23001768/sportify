<?php

namespace Models;

use Core\Database;
use PDO;

class EventInvitation
{
    public static function createInvitation($eventId, $email)
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

    public static function findInvitationByToken($token)
    {
        $sql = "SELECT * FROM EVENT_INVITATIONS WHERE token = :token";
        $params = [':token' => $token];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public static function findInvitationsByEventId($eventId)
    {
        $sql = "SELECT * FROM EVENT_INVITATIONS WHERE event_id = :event_id";
        $params = [':event_id' => $eventId];
        return Database::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findInvitationById($invitationId)
    {
        $sql = "SELECT * FROM EVENT_INVITATIONS WHERE invitation_id = :invitation_id";
        $params = [':invitation_id' => $invitationId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }
    

    public static function deleteInvitation($invitationId)
    {
        $sql = "DELETE FROM EVENT_INVITATIONS WHERE invitation_id = :invitation_id";
        $params = [':invitation_id' => $invitationId];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function findInvitationsByEventIdAndEmail($eventId, $email)
    {
        $sql = "SELECT * FROM EVENT_INVITATIONS WHERE event_id = :event_id AND email = :email";
        $params = [
            ':event_id' => $eventId,
            ':email' => $email
        ];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

}