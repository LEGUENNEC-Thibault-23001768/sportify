<?php

namespace Models;

use Core\Database;
use PDO;

class Tournament
{
    // Status enum values
    const STATUS_LOCKED = 0;
    const STATUS_WAITING = 1;
    const STATUS_READY = 2;
    const STATUS_RUNNING = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_ARCHIVED = 5;

    public static function create($name, $sportType, $startDate, $endDate, $description, $maxTeams, $createdBy, $format = 'knockout', $location = null)
    {
        $sql = "INSERT INTO TOURNAMENT (tournament_name, sport_type, start_date, end_date, description, max_teams, created_by_member_id, tournament_format, location)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$name, $sportType, $startDate, $endDate, $description, $maxTeams, $createdBy, $format, $location];
        Database::query($sql, $params);
        return Database::getConnection()->lastInsertId();
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

    public static function update($tournamentId, $data)
    {
        $sql = "UPDATE TOURNAMENT SET ";
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            $params[] = $value;
        }

        $sql .= implode(", ", $updates);
        $sql .= " WHERE tournament_id = ?";
        $params[] = $tournamentId;

        return Database::query($sql, $params);
    }

    public static function setTournamentStatus($tournamentId, $status) {
        $sql = "UPDATE TOURNAMENT SET status = ? WHERE tournament_id = ?";
        return Database::query($sql, [$status, $tournamentId]);
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
        $sql = "INSERT INTO TEAM_MEMBERS (tournament_team_id, member_id) VALUES (?, ?)";
        Database::query($sql, [$teamId, $memberId]);
    }

    public static function generateKnockoutBracket($tournamentId)
    {
        $teams = self::getTeams($tournamentId);
        $tournament = self::getById($tournamentId);
        $numTeams = count($teams);

        if ($numTeams > $tournament['max_teams']) {
            throw new \Exception('Number of teams exceeds the maximum allowed for this tournament.');
        }

        $pow2 = 1;
        while ($pow2 < $numTeams) {
            $pow2 *= 2;
        }
        $byeTeamsNeeded = $pow2 - $numTeams;

        shuffle($teams);

        $numRounds = log($pow2, 2);
        $matchIdCounter = 1;

        $bracketData = [
            "participant" => [],
            "stage" => [],
            "group" => [],
            "round" => [],
            "match" => [],
            "match_game" => []
        ];

        foreach ($teams as $team) {
            $bracketData["participant"][] = [
                "id" => $team['tournament_team_id'],
                "tournament_id" => $tournamentId,
                "name" => $team['team_name']
            ];
        }

        $stageId = 1;
        $bracketData["stage"][] = [
            "id" => $stageId,
            "tournament_id" => $tournamentId,
            "name" => "Main Stage",
            "type" => "single_elimination",
            "number" => 1,
            "settings" => [
                "size" => $pow2,
                "seedOrdering" => ["natural"],
                "grandFinal" => "single",
                "matchesChildCount" => 0
            ]
        ];

        $groupId = 1;
        $bracketData["group"][] = [
            "id" => $groupId,
            "stage_id" => $stageId,
            "number" => 1
        ];

        // Interleave bye teams to the available teams
        for ($i = 0; $i < $byeTeamsNeeded; $i++) {
            $teams[] =  ["tournament_team_id" => null, "team_name" => 'Bye'];
        }

        for ($round = 1; $round <= $numRounds; $round++) {
            $roundId = $round;
            $bracketData["round"][] = [
                "id" => $roundId,
                "stage_id" => $stageId,
                "group_id" => $groupId,
                "number" => $round
            ];

            $numMatchesInRound = $pow2 / pow(2, $round);

            for ($matchNumber = 1; $matchNumber <= $numMatchesInRound; $matchNumber++) {
                $team1Id = null;
                $team2Id = null;

                if (count($teams) > 0) {
                    $team1 = array_shift($teams);
                    $team1Id = $team1['tournament_team_id'];
                }

                if (count($teams) > 0) {
                    $team2 = array_shift($teams);
                    $team2Id = $team2['tournament_team_id'];
                }

                // Set opponent IDs to null for byes
                $opponent1 = ["id" => $team1Id];
                $opponent2 = ["id" => $team2Id];

                $bracketData["match"][] = [
                    "id" => $matchIdCounter,
                    "stage_id" => $stageId,
                    "group_id" => $groupId,
                    "round_id" => $roundId,
                    "number" => $matchNumber,
                    "child_count" => 0,
                    "status" => self::STATUS_LOCKED,
                    "opponent1" => $opponent1,
                    "opponent2" => $opponent2,
                ];

                $matchIdCounter++;
            }
        }

        return $bracketData;
    }

    public static function getBracketData($tournamentId)
    {
        $sql = "SELECT bracket_data FROM TOURNAMENT WHERE tournament_id = ?";
        $result = Database::query($sql, [$tournamentId])->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['bracket_data']) {
            return json_decode($result['bracket_data'], true);
        } else {
            return null; // Or return a default empty bracket structure
        }
    }

    public static function saveBracketData($tournamentId, $bracketData)
    {
        $bracketJson = json_encode($bracketData);
        $sql = "UPDATE TOURNAMENT SET bracket_data = ? WHERE tournament_id = ?";
        return Database::query($sql, [$bracketJson, $tournamentId]);
    }

    public static function propagateWinner($tournamentId, $matchId, $bracketData)
{
    // Find the completed match in the bracket data
    $foundMatch = null;
    foreach ($bracketData['match'] as &$match) {
        if ($match['id'] == $matchId && $match['status'] == self::STATUS_COMPLETED) {
            $foundMatch = &$match;
            break;
        }
    }

    if (!$foundMatch || !isset($foundMatch['winner_id'])) {
        return $bracketData;
    }

    $winnerId = $foundMatch['winner_id'];
    $currentRoundNumber = $foundMatch['round_id'];
    $currentMatchNumber = $foundMatch['number'];

    // Calculate next round and match details
    $nextRoundNumber = $currentRoundNumber + 1;
    $nextMatchNumber = (int)ceil($currentMatchNumber / 2);
    $slot = ($currentMatchNumber % 2 === 1) ? 'opponent1' : 'opponent2';

    // Find the next round
    $nextRoundExists = false;
    foreach ($bracketData['round'] as $round) {
        if ($round['id'] == $nextRoundNumber) {
            $nextRoundExists = true;
            break;
        }
    }
    if (!$nextRoundExists) {
        return $bracketData; // No next round (e.g., final match)
    }

    // Find the specific next match
    foreach ($bracketData['match'] as &$nextMatch) {
        if ($nextMatch['round_id'] == $nextRoundNumber && $nextMatch['number'] == $nextMatchNumber) {
            // Clear previous winner from this slot if it's the same as the old winner
            if ($nextMatch[$slot]['id'] === $foundMatch['winner_id']) {
                $nextMatch[$slot]['id'] = null;
                $nextMatch['status'] = self::STATUS_LOCKED;
            }

            // Set the new winner
            $nextMatch[$slot]['id'] = $winnerId;

            // Update match status if both opponents are set
            if ($nextMatch['opponent1']['id'] !== null && $nextMatch['opponent2']['id'] !== null) {
                $nextMatch['status'] = self::STATUS_READY;
            } elseif ($nextMatch['opponent1']['id'] !== null || $nextMatch['opponent2']['id'] !== null) {
                $nextMatch['status'] = self::STATUS_WAITING;
            }

            break;
        }
    }

    return $bracketData;
}

    public static function updateMatchScore($tournamentId, $matchId, $team1Score = null, $team2Score = null, $winnerId = null) {
        $bracketData = self::getBracketData($tournamentId);

        // find the match with the ID
        foreach ($bracketData['match'] as &$match) {
            if ($match['id'] == $matchId) {

                $match['opponent1'] = [
                    'id' => $match['opponent1']['id'],
                    'score' => $team1Score
                ];
                $match['opponent2'] = [
                    'id' => $match['opponent2']['id'],
                    'score' => $team2Score
                ];

                $match['winner_id'] = null;
                $match['status'] = self::STATUS_COMPLETED;
                if ($team1Score > $team2Score) {
                    $winnerId  = $match['opponent1']['id'];
                    $match['opponent1']['result'] = 'win';
                    $match['opponent2']['result'] = 'loss';
                } else{
                    $winnerId  = $match['opponent2']['id'];
                    $match['opponent2']['result'] = 'win';
                    $match['opponent1']['result'] = 'loss';
                }
                $match['winner_id'] = $winnerId;
            }
        }

        $bracketData = self::propagateWinner($tournamentId, $matchId, $bracketData);

        return $bracketData;
    }
}