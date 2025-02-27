<?php

namespace Models;

use Core\Database;
use PDO;

class Tournament
{
    public static function create($name, $sportType, $startDate, $endDate, $description, $maxTeams, $createdBy, $format = 'knockout', $location = null)
    {
        $sql = "INSERT INTO TOURNAMENT (tournament_name, sport_type, start_date, end_date, description, max_teams, created_by_member_id, tournament_format, location)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$name, $sportType, $startDate, $endDate, $description, $maxTeams, $createdBy, $format, $location];
        return Database::query($sql, $params);
    }

    public static function getAll()
    {
        $sql = "SELECT t.*, m.last_name as creator_name 
                FROM TOURNAMENT t
                JOIN MEMBER m ON t.created_by_member_id = m.member_id
                ORDER BY start_date DESC";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($tournamentId)
    {
        $sql = "SELECT t.*, m.last_name as creator_name 
                FROM TOURNAMENT t
                JOIN MEMBER m ON t.created_by_member_id = m.member_id
                WHERE tournament_id = ?";
        return Database::query($sql, [$tournamentId])->fetch(PDO::FETCH_ASSOC);
    }

    public static function addTeam($tournamentId, $teamName, $leaderId = null, $generalTeamId = null)
    {
        $sql = "INSERT INTO TOURNAMENT_TEAM (tournament_id, team_name, team_leader_member_id, general_team_id)
                VALUES (?, ?, ?, ?)";
        $params = [$tournamentId, $teamName, $leaderId, $generalTeamId];
        return Database::query($sql, $params);
    }

    public static function getTeams($tournamentId)
    {
        $sql = "SELECT tt.*, m.last_name as leader_name, t.team_name as general_team_name
                FROM TOURNAMENT_TEAM tt
                LEFT JOIN MEMBER m ON tt.team_leader_member_id = m.member_id
                LEFT JOIN TEAM t ON tt.general_team_id = t.team_id
                WHERE tt.tournament_id = ?";
        return Database::query($sql, [$tournamentId])->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function generateInviteToken($teamId)
    {
        $token = bin2hex(random_bytes(32));
        $sql = "UPDATE TOURNAMENT_TEAM SET invite_token = ? WHERE tournament_team_id = ?";
        Database::query($sql, [$token, $teamId]);
        return $token;
    }

    public static function getByInviteToken($token)
    {
        $sql = "SELECT * FROM TOURNAMENT_TEAM WHERE invite_token = ?";
        return Database::query($sql, [$token])->fetch(PDO::FETCH_ASSOC);
    }

    public static function addParticipant($teamId, $memberId)
    {
    }
}
?>