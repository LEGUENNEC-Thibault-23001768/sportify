<?php

namespace Models;

use Core\Database;

class TeamParticipant
{
    // Add a member to a team
    public static function addParticipant($team_id, $member_id)
    {
        $sql = "INSERT INTO TEAM_PARTICIPANT (team_id, member_id) VALUES (:team_id, :member_id)";
        $params = [
            ':team_id' => $team_id,
            ':member_id' => $member_id
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    // Get all members of a team
    public static function getMembersByTeam($team_id)
    {
        $sql = "SELECT * FROM TEAM_PARTICIPANT JOIN MEMBER ON TEAM_PARTICIPANT.member_id = MEMBER.member_id WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        return Database::query($sql, $params)->fetchAll();
    }

    // Delete all participants of a team
    public static function deleteParticipantsByTeam($team_id)
    {
        $sql = "DELETE FROM TEAM_PARTICIPANT WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        return Database::query($sql, $params)->rowCount() > 0;
    }
}