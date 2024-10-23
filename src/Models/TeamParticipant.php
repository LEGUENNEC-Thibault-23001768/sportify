<?php

namespace Models;

use Core\Database;

class TeamParticipant
{

    // Ajouter un membre à une équipe
    public function addParticipant($team_id, $member_id)
    {
        return Database::getInstance()->getConnection()->query("INSERT INTO TEAM_PARTICIPANT (team_id, member_id) VALUES (:team_id, :member_id)");
    }

    // Obtenir tous les membres d'une équipe
    public static function getMembersByTeam($team_id)
    {
        return Database::getInstance()->getConnection()->query("SELECT * FROM TEAM_PARTICIPANT JOIN MEMBER ON TEAM_PARTICIPANT.member_id = MEMBER.member_id WHERE team_id = :team_id");
    }

    // Supprimer tous les participants d'une équipe
    public static function deleteParticipantsByTeam($team_id)
    {
        return Database::getInstance()->getConnection()->query("DELETE FROM TEAM_PARTICIPANT WHERE team_id = :team_id");
    }
}
