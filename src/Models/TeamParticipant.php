<?php

namespace Models;

use Core\Database;

class TeamParticipant
{
    // Add a member to a team
    /**
     * @param int $team_id
     * @param int $member_id
     * @return bool
     */
    public static function addParticipant(int $team_id, int $member_id): bool
    {
        $sql = "INSERT INTO TEAM_PARTICIPANT (team_id, member_id) VALUES (:team_id, :member_id)";
        $params = [
            ':team_id' => $team_id,
            ':member_id' => $member_id
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    // Get all members of a team

    /**
     * @param int $team_id
     * @return array
     */
    public static function getMembersByTeam(int $team_id): array
    {
        $sql = "SELECT * FROM TEAM_PARTICIPANT JOIN MEMBER ON TEAM_PARTICIPANT.member_id = MEMBER.member_id WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        return Database::query($sql, $params)->fetchAll();
    }

    // Delete all participants of a team

    /**
     * @param int $team_id
     * @return bool
     */
    public static function deleteParticipantsByTeam(int $team_id): bool
    {
        $sql = "DELETE FROM TEAM_PARTICIPANT WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        return Database::query($sql, $params)->rowCount() > 0;
    }
}