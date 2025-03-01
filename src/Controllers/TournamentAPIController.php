<?php

namespace Controllers;

use Core\APIController;
use Core\APIResponse;
use Models\Tournament;
use Models\User;
use Core\Router;
use Core\RouteProvider;
use Core\Auth;
use Core\Database;

class TournamentAPIController extends APIController implements RouteProvider
{
    public static function routes(): void
    {
        Router::apiResource('/api/tournaments', self::class, Auth::requireLogin());
        Router::post('/api/tournaments/{id}/teams', self::class . '@addTeam', Auth::requireLogin());
        Router::get('/api/tournaments/{id}/bracket', self::class . '@getBracket');
        Router::post('/api/tournaments/{id}/generate-bracket', self::class . '@generateBracket', Auth::requireLogin());
        Router::get('/api/tournaments/invite/{token}', self::class . '@handleInvite');
        Router::put('/api/tournaments/{id}/matches/{matchId}', self::class . '@updateMatchScore', Auth::requireLogin());
        Router::get('/api/tournaments/{id}/showteams', self::class . '@showTeams', Auth::requireLogin());
        Router::post('/api/tournaments/{id}/addTeams', self::class . '@addTeams', Auth::requireLogin());
    }

    public function get($id = null)
    {
        $response = new APIResponse();

        if ($id) {
            $tournament = Tournament::getById($id);
            if (!$tournament) {
                return $response->setStatusCode(404)->setData(['error' => 'Tournament not found'])->send();
            }
            return $response->setData($tournament)->send();
        }

        $tournaments = Tournament::getAll();
        return $response->setData(['tournaments' => $tournaments])->send();
    }

    public function post()
    {
        $response = new APIResponse();
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $_SESSION['user_id'];

        $user = User::getUserById($userId);
        if ($user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized'])->send();
        }

        $required = ['name', 'sportType', 'startDate', 'endDate', 'maxTeams'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $response->setStatusCode(400)->setData(['error' => "Missing $field"])->send();
            }
        }

        $tournamentId = Tournament::create(
            $data['name'],
            $data['sportType'],
            $data['startDate'],
            $data['endDate'],
            $data['description'] ?? '',
            $data['maxTeams'],
            $userId,
            $data['format'] ?? 'knockout',
            $data['location'] ?? null
        );

        $teamList = $data['teamList'] ?? [];
        foreach ($teamList as $teamName) {
            Tournament::addTeam($tournamentId, trim($teamName), $userId); // Add team, using logged in user as team leader
        }

        $bracketData = Tournament::generateKnockoutBracket($tournamentId);
        Tournament::saveBracketData($tournamentId, $bracketData);

        return $response->setStatusCode(201)->setData([
            'message' => 'Tournament created',
            'tournamentId' => $tournamentId
        ])->send();
    }

    public function put($id = null) {
        $response = new APIResponse();
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $_SESSION['user_id'];

        $user = User::getUserById($userId);
        if ($user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized'])->send();
        }

        Tournament::update($id, $data);

        return $response->setData(['message' => 'Tournament updated'])->send();
    }

    public function delete($id = null) {
        $response = new APIResponse();
        $userId = $_SESSION['user_id'];

        $user = User::getUserById($userId);
        if ($user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized'])->send();
        }

        $sql = "DELETE FROM TOURNAMENT WHERE tournament_id = ?";
        Database::query($sql, [$id]);


        return $response->setData(['message' => 'Tournament deleted'])->send();
    }

    public function showTeams($tournamentId) {
        $response = new APIResponse();
        $userId = $_SESSION['user_id'];
    
        $user = User::getUserById($userId);
        if ($user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized'])->send();
        }
    
        $tournament = Tournament::getById($tournamentId);
        if (!$tournament) {
            return $response->setStatusCode(404)->setData(['error' => 'Tournament not found'])->send();
        }
    
        $teams = Tournament::getTeams($tournamentId);
        return $response->setData(['teams' => $teams, 'tournament' => $tournament])->send();
    }

    public function addTeams($tournamentId) {
        $response = new APIResponse();
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $_SESSION['user_id'];
    
        $user = User::getUserById($userId);
        if ($user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized'])->send();
        }
    
        $tournament = Tournament::getById($tournamentId);
        if (!$tournament) {
            return $response->setStatusCode(404)->setData(['error' => 'Tournament not found'])->send();
        }
    
        $teamList = $data['teamList'] ?? [];
        $currentTeamCount = count(Tournament::getTeams($tournamentId));
        $newTeamCount = $currentTeamCount + count($teamList);
        if ($newTeamCount > $tournament['max_teams']) {
            return $response->setStatusCode(400)->setData(['error' => 'Exceeds max teams'])->send();
        }
    
        foreach ($teamList as $teamName) {
            Tournament::addTeam($tournamentId, trim($teamName), $userId);
        }
    
        $bracketData = Tournament::generateKnockoutBracket($tournamentId);
        Tournament::saveBracketData($tournamentId, $bracketData);
    
        return $response->setData(['message' => 'Teams added successfully'])->send();
    }

    public function addTeam($tournamentId)
    {
        $response = new APIResponse();
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $_SESSION['user_id'];

        if (empty($data['teamName'])) {
            return $response->setStatusCode(400)->setData(['error' => 'Missing team name'])->send();
        }

        $teamId = Tournament::addTeam(
            $tournamentId,
            $data['teamName'],
            $userId,
            $data['generalTeamId'] ?? null
        );

        $inviteToken = Tournament::generateInviteToken($teamId);

        return $response->setData([
            'message' => 'Team added',
            'teamId' => $teamId,
            'inviteLink' => "/api/tournaments/invite/$inviteToken"
        ])->send();
    }

    public function getBracket($tournamentId)
    {
        $response = new APIResponse();
        $bracketData = Tournament::getBracketData($tournamentId);

        if (!$bracketData) {
            return $response->setStatusCode(404)->setData(['error' => 'Bracket data not found.  Generate the bracket first.'])->send();
        }

        return $response->setData($bracketData)->send();
    }

    public function generateBracket($tournamentId)
    {
        $response = new APIResponse();
        $userId = $_SESSION['user_id'];

        $user = User::getUserById($userId);
        if ($user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized'])->send();
        }

        $bracketData = Tournament::generateKnockoutBracket($tournamentId);
        Tournament::saveBracketData($tournamentId, $bracketData);

        return $response->setData(['message' => 'Bracket generated'])->send();
    }

    public function handleInvite($token)
    {
        $response = new APIResponse();
        $team = Tournament::getByInviteToken($token);

        if (!$team) {
            return $response->setStatusCode(404)->setData(['error' => 'Invalid invite link'])->send();
        }

        if (!isset($_SESSION['user_id'])) {
            return $response->setStatusCode(401)->setData(['error' => 'Login required'])->send();
        }

        Tournament::addParticipant($team['tournament_team_id'], $_SESSION['user_id']);

        return $response->setData(['message' => 'Joined team successfully'])->send();
    }

    public function updateMatchScore($tournamentId, $matchId) {
        $response = new APIResponse();
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $_SESSION['user_id'];

        $user = User::getUserById($userId);
        if ($user['status'] !== 'admin') {
            return $response->setStatusCode(403)->setData(['error' => 'User not authorized'])->send();
        }

        $team1Score = $data['team1_score'] ?? null;
        $team2Score = $data['team2_score'] ?? null;
        $winnerId = $data['winner_id'] ?? null;

        $bracketData = Tournament::updateMatchScore($tournamentId, $matchId, $team1Score, $team2Score, $winnerId);
        Tournament::saveBracketData($tournamentId, $bracketData);

        return $response->setData(['message' => 'Match score updated'])->send();
    }
}