<?php

namespace Controllers;

use Core\APIController;
use Core\APIResponse;
use Models\Tournament;
use Models\User;
use Core\Router;
use Core\RouteProvider;
use Core\Auth;

class TournamentAPIController extends APIController implements RouteProvider
{
    public static function routes(): void
    {
        Router::apiResource('/api/tournaments', self::class, Auth::requireLogin());
        Router::post('/api/tournaments/{id}/teams', self::class . '@addTeam', Auth::requireLogin());
        Router::get('/api/tournaments/{id}/bracket', self::class . '@getBracket');
        Router::post('/api/tournaments/{id}/generate-bracket', self::class . '@generateBracket', Auth::requireLogin());
        Router::get('/api/tournaments/invite/{token}', self::class . '@handleInvite');
    }

    public function get($id = null)
    {
        $response = new APIResponse();
        $user = User::getUserById($_SESSION['user_id']);

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

        $required = ['name', 'sportType', 'startDate', 'endDate'];
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
            $data['maxTeams'] ?? null,
            $userId,
            $data['format'] ?? 'knockout',
            $data['location'] ?? null
        );

        return $response->setStatusCode(201)->setData([
            'message' => 'Tournament created',
            'tournamentId' => $tournamentId
        ])->send();
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
        $tournament = Tournament::getById($tournamentId);
        
        // Example bracket structure for brackets-viewer.js
        $bracketData = [
            "participant" => [
                  [
                     "id" => 0, 
                     "tournament_id" => 0, 
                     "name" => "Team 1" 
                  ], 
                  [
                        "id" => 1, 
                        "tournament_id" => 0, 
                        "name" => "Team 2" 
                     ], 
                  [
                           "id" => 2, 
                           "tournament_id" => 0, 
                           "name" => "Team 3" 
                        ], 
                  [
                              "id" => 3, 
                              "tournament_id" => 0, 
                              "name" => "Team 4" 
                           ], 
                  [
                                 "id" => 4, 
                                 "tournament_id" => 0, 
                                 "name" => "Team 6" 
                              ], 
                  [
                                    "id" => 5, 
                                    "tournament_id" => 0, 
                                    "name" => "Team 7" 
                                 ], 
                  [
                                       "id" => 6, 
                                       "tournament_id" => 0, 
                                       "name" => "Team 8" 
                                    ], 
                  [
                                          "id" => 7, 
                                          "tournament_id" => 0, 
                                          "name" => "Team 9" 
                                       ], 
                  [
                                             "id" => 8, 
                                             "tournament_id" => 0, 
                                             "name" => "Team 10" 
                                          ], 
                  [
                                                "id" => 9, 
                                                "tournament_id" => 0, 
                                                "name" => "Team 11" 
                                             ], 
                  [
                                                   "id" => 10, 
                                                   "tournament_id" => 0, 
                                                   "name" => "Team 12" 
                                                ], 
                  [
                                                      "id" => 11, 
                                                      "tournament_id" => 0, 
                                                      "name" => "Team 13" 
                                                   ], 
                  [
                                                         "id" => 12, 
                                                         "tournament_id" => 0, 
                                                         "name" => "Team 14" 
                                                      ], 
                  [
                                                            "id" => 13, 
                                                            "tournament_id" => 0, 
                                                            "name" => "Team 15" 
                                                         ], 
                  [
                                                               "id" => 14, 
                                                               "tournament_id" => 0, 
                                                               "name" => "Team 16" 
                                                            ] 
               ], 
            "stage" => [
                                                                  [
                                                                     "id" => 0, 
                                                                     "tournament_id" => 0, 
                                                                     "name" => "Example", 
                                                                     "type" => "double_elimination", 
                                                                     "number" => 1, 
                                                                     "settings" => [
                                                                        "size" => 16, 
                                                                        "seedOrdering" => [
                                                                           "natural", 
                                                                           "natural", 
                                                                           "reverse_half_shift", 
                                                                           "reverse" 
                                                                        ], 
                                                                        "grandFinal" => "double", 
                                                                        "matchesChildCount" => 0 
                                                                     ] 
                                                                  ] 
                                                               ], 
            "group" => [
                                                                              [
                                                                                 "id" => 0, 
                                                                                 "stage_id" => 0, 
                                                                                 "number" => 1 
                                                                              ], 
                                                                              [
                                                                                    "id" => 1, 
                                                                                    "stage_id" => 0, 
                                                                                    "number" => 2 
                                                                                 ], 
                                                                              [
                                                                                       "id" => 2, 
                                                                                       "stage_id" => 0, 
                                                                                       "number" => 3 
                                                                                    ] 
                                                                           ], 
            "round" => [
                                                                                          [
                                                                                             "id" => 0, 
                                                                                             "number" => 1, 
                                                                                             "stage_id" => 0, 
                                                                                             "group_id" => 0 
                                                                                          ], 
                                                                                          [
                                                                                                "id" => 1, 
                                                                                                "number" => 2, 
                                                                                                "stage_id" => 0, 
                                                                                                "group_id" => 0 
                                                                                             ], 
                                                                                          [
                                                                                                   "id" => 2, 
                                                                                                   "number" => 3, 
                                                                                                   "stage_id" => 0, 
                                                                                                   "group_id" => 0 
                                                                                                ], 
                                                                                          [
                                                                                                      "id" => 3, 
                                                                                                      "number" => 4, 
                                                                                                      "stage_id" => 0, 
                                                                                                      "group_id" => 0 
                                                                                                   ], 
                                                                                          [
                                                                                                         "id" => 4, 
                                                                                                         "number" => 1, 
                                                                                                         "stage_id" => 0, 
                                                                                                         "group_id" => 1 
                                                                                                      ], 
                                                                                          [
                                                                                                            "id" => 5, 
                                                                                                            "number" => 2, 
                                                                                                            "stage_id" => 0, 
                                                                                                            "group_id" => 1 
                                                                                                         ], 
                                                                                          [
                                                                                                               "id" => 6, 
                                                                                                               "number" => 3, 
                                                                                                               "stage_id" => 0, 
                                                                                                               "group_id" => 1 
                                                                                                            ], 
                                                                                          [
                                                                                                                  "id" => 7, 
                                                                                                                  "number" => 4, 
                                                                                                                  "stage_id" => 0, 
                                                                                                                  "group_id" => 1 
                                                                                                               ], 
                                                                                          [
                                                                                                                     "id" => 8, 
                                                                                                                     "number" => 5, 
                                                                                                                     "stage_id" => 0, 
                                                                                                                     "group_id" => 1 
                                                                                                                  ], 
                                                                                          [
                                                                                                                        "id" => 9, 
                                                                                                                        "number" => 6, 
                                                                                                                        "stage_id" => 0, 
                                                                                                                        "group_id" => 1 
                                                                                                                     ], 
                                                                                          [
                                                                                                                           "id" => 10, 
                                                                                                                           "number" => 1, 
                                                                                                                           "stage_id" => 0, 
                                                                                                                           "group_id" => 2 
                                                                                                                        ], 
                                                                                          [
                                                                                                                              "id" => 11, 
                                                                                                                              "number" => 2, 
                                                                                                                              "stage_id" => 0, 
                                                                                                                              "group_id" => 2 
                                                                                                                           ] 
                                                                                       ], 
            "match" => [
                                                                                                                                 [
                                                                                                                                    "id" => 0, 
                                                                                                                                    "number" => 1, 
                                                                                                                                    "stage_id" => 0, 
                                                                                                                                    "group_id" => 0, 
                                                                                                                                    "round_id" => 0, 
                                                                                                                                    "child_count" => 0, 
                                                                                                                                    "status" => 2, 
                                                                                                                                    "opponent1" => [
                                                                                                                                       "id" => 0, 
                                                                                                                                       "position" => 1, 
                                                                                                                                       "score" => 16, 
                                                                                                                                       "result" => "win" 
                                                                                                                                    ], 
                                                                                                                                    "opponent2" => [
                                                                                                                                          "id" => 1, 
                                                                                                                                          "position" => 2, 
                                                                                                                                          "score" => 12, 
                                                                                                                                          "result" => "loss" 
                                                                                                                                       ] 
                                                                                                                                 ], 
                                                                                                                                 [
                                                                                                                                             "id" => 1, 
                                                                                                                                             "number" => 2, 
                                                                                                                                             "stage_id" => 0, 
                                                                                                                                             "group_id" => 0, 
                                                                                                                                             "round_id" => 0, 
                                                                                                                                             "child_count" => 0, 
                                                                                                                                             "status" => 2, 
                                                                                                                                             "opponent1" => [
                                                                                                                                                "id" => 2, 
                                                                                                                                                "position" => 3, 
                                                                                                                                                "score" => 8 
                                                                                                                                             ], 
                                                                                                                                             "opponent2" => [
                                                                                                                                                   "id" => 3, 
                                                                                                                                                   "position" => 4, 
                                                                                                                                                   "score" => 4 
                                                                                                                                                ] 
                                                                                                                                          ], 
                                                                                                                                 [
                                                                                                                                                      "id" => 2, 
                                                                                                                                                      "number" => 3, 
                                                                                                                                                      "stage_id" => 0, 
                                                                                                                                                      "group_id" => 0, 
                                                                                                                                                      "round_id" => 0, 
                                                                                                                                                      "child_count" => 0, 
                                                                                                                                                      "status" => 0, 
                                                                                                                                                      "opponent1" => null, 
                                                                                                                                                      "opponent2" => [
                                                                                                                                                         "id" => 4, 
                                                                                                                                                         "position" => 6 
                                                                                                                                                      ] 
                                                                                                                                                   ], 
                                                                                                                                 [
                                                                                                                                                            "id" => 3, 
                                                                                                                                                            "number" => 4, 
                                                                                                                                                            "stage_id" => 0, 
                                                                                                                                                            "group_id" => 0, 
                                                                                                                                                            "round_id" => 0, 
                                                                                                                                                            "child_count" => 0, 
                                                                                                                                                            "status" => 2, 
                                                                                                                                                            "opponent1" => [
                                                                                                                                                               "id" => 5, 
                                                                                                                                                               "position" => 7 
                                                                                                                                                            ], 
                                                                                                                                                            "opponent2" => [
                                                                                                                                                                  "id" => 6, 
                                                                                                                                                                  "position" => 8 
                                                                                                                                                               ] 
                                                                                                                                                         ], 
                                                                                                                                 [
                                                                                                                                                                     "id" => 4, 
                                                                                                                                                                     "number" => 5, 
                                                                                                                                                                     "stage_id" => 0, 
                                                                                                                                                                     "group_id" => 0, 
                                                                                                                                                                     "round_id" => 0, 
                                                                                                                                                                     "child_count" => 0, 
                                                                                                                                                                     "status" => 2, 
                                                                                                                                                                     "opponent1" => [
                                                                                                                                                                        "id" => 7, 
                                                                                                                                                                        "position" => 9 
                                                                                                                                                                     ], 
                                                                                                                                                                     "opponent2" => [
                                                                                                                                                                           "id" => 8, 
                                                                                                                                                                           "position" => 10 
                                                                                                                                                                        ] 
                                                                                                                                                                  ], 
                                                                                                                                 [
                                                                                                                                                                              "id" => 5, 
                                                                                                                                                                              "number" => 6, 
                                                                                                                                                                              "stage_id" => 0, 
                                                                                                                                                                              "group_id" => 0, 
                                                                                                                                                                              "round_id" => 0, 
                                                                                                                                                                              "child_count" => 0, 
                                                                                                                                                                              "status" => 2, 
                                                                                                                                                                              "opponent1" => [
                                                                                                                                                                                 "id" => 9, 
                                                                                                                                                                                 "position" => 11 
                                                                                                                                                                              ], 
                                                                                                                                                                              "opponent2" => [
                                                                                                                                                                                    "id" => 10, 
                                                                                                                                                                                    "position" => 12 
                                                                                                                                                                                 ] 
                                                                                                                                                                           ], 
                                                                                                                                 [
                                                                                                                                                                                       "id" => 6, 
                                                                                                                                                                                       "number" => 7, 
                                                                                                                                                                                       "stage_id" => 0, 
                                                                                                                                                                                       "group_id" => 0, 
                                                                                                                                                                                       "round_id" => 0, 
                                                                                                                                                                                       "child_count" => 0, 
                                                                                                                                                                                       "status" => 2, 
                                                                                                                                                                                       "opponent1" => [
                                                                                                                                                                                          "id" => 11, 
                                                                                                                                                                                          "position" => 13 
                                                                                                                                                                                       ], 
                                                                                                                                                                                       "opponent2" => [
                                                                                                                                                                                             "id" => 12, 
                                                                                                                                                                                             "position" => 14 
                                                                                                                                                                                          ] 
                                                                                                                                                                                    ], 
                                                                                                                                 [
                                                                                                                                                                                                "id" => 7, 
                                                                                                                                                                                                "number" => 8, 
                                                                                                                                                                                                "stage_id" => 0, 
                                                                                                                                                                                                "group_id" => 0, 
                                                                                                                                                                                                "round_id" => 0, 
                                                                                                                                                                                                "child_count" => 0, 
                                                                                                                                                                                                "status" => 2, 
                                                                                                                                                                                                "opponent1" => [
                                                                                                                                                                                                   "id" => 13, 
                                                                                                                                                                                                   "position" => 15 
                                                                                                                                                                                                ], 
                                                                                                                                                                                                "opponent2" => [
                                                                                                                                                                                                      "id" => null, 
                                                                                                                                                                                                      "position" => 16 
                                                                                                                                                                                                   ] 
                                                                                                                                                                                             ], 
                                                                                                                                 [
                                                                                                                                                                                                         "id" => 8, 
                                                                                                                                                                                                         "number" => 1, 
                                                                                                                                                                                                         "stage_id" => 0, 
                                                                                                                                                                                                         "group_id" => 0, 
                                                                                                                                                                                                         "round_id" => 1, 
                                                                                                                                                                                                         "child_count" => 0, 
                                                                                                                                                                                                         "status" => 1, 
                                                                                                                                                                                                         "opponent1" => [
                                                                                                                                                                                                            "id" => 0 
                                                                                                                                                                                                         ], 
                                                                                                                                                                                                         "opponent2" => [
                                                                                                                                                                                                               "id" => null 
                                                                                                                                                                                                            ] 
                                                                                                                                                                                                      ], 
                                                                                                                                 [
                                                                                                                                                                                                                  "id" => 9, 
                                                                                                                                                                                                                  "number" => 2, 
                                                                                                                                                                                                                  "stage_id" => 0, 
                                                                                                                                                                                                                  "group_id" => 0, 
                                                                                                                                                                                                                  "round_id" => 1, 
                                                                                                                                                                                                                  "child_count" => 0, 
                                                                                                                                                                                                                  "status" => 1, 
                                                                                                                                                                                                                  "opponent1" => [
                                                                                                                                                                                                                     "id" => 4 
                                                                                                                                                                                                                  ], 
                                                                                                                                                                                                                  "opponent2" => [
                                                                                                                                                                                                                        "id" => null 
                                                                                                                                                                                                                     ] 
                                                                                                                                                                                                               ], 
                                                                                                                                 [
                                                                                                                                                                                                                           "id" => 10, 
                                                                                                                                                                                                                           "number" => 3, 
                                                                                                                                                                                                                           "stage_id" => 0, 
                                                                                                                                                                                                                           "group_id" => 0, 
                                                                                                                                                                                                                           "round_id" => 1, 
                                                                                                                                                                                                                           "child_count" => 0, 
                                                                                                                                                                                                                           "status" => 0, 
                                                                                                                                                                                                                           "opponent1" => [
                                                                                                                                                                                                                              "id" => null 
                                                                                                                                                                                                                           ], 
                                                                                                                                                                                                                           "opponent2" => [
                                                                                                                                                                                                                                 "id" => null 
                                                                                                                                                                                                                              ] 
                                                                                                                                                                                                                        ], 
                                                                                                                                 [
                                                                                                                                                                                                                                    "id" => 11, 
                                                                                                                                                                                                                                    "number" => 4, 
                                                                                                                                                                                                                                    "stage_id" => 0, 
                                                                                                                                                                                                                                    "group_id" => 0, 
                                                                                                                                                                                                                                    "round_id" => 1, 
                                                                                                                                                                                                                                    "child_count" => 0, 
                                                                                                                                                                                                                                    "status" => 0, 
                                                                                                                                                                                                                                    "opponent1" => [
                                                                                                                                                                                                                                       "id" => null 
                                                                                                                                                                                                                                    ], 
                                                                                                                                                                                                                                    "opponent2" => [
                                                                                                                                                                                                                                          "id" => null 
                                                                                                                                                                                                                                       ] 
                                                                                                                                                                                                                                 ], 
                                                                                                                                 [
                                                                                                                                                                                                                                             "id" => 12, 
                                                                                                                                                                                                                                             "number" => 1, 
                                                                                                                                                                                                                                             "stage_id" => 0, 
                                                                                                                                                                                                                                             "group_id" => 0, 
                                                                                                                                                                                                                                             "round_id" => 2, 
                                                                                                                                                                                                                                             "child_count" => 0, 
                                                                                                                                                                                                                                             "status" => 0, 
                                                                                                                                                                                                                                             "opponent1" => [
                                                                                                                                                                                                                                                "id" => null 
                                                                                                                                                                                                                                             ], 
                                                                                                                                                                                                                                             "opponent2" => [
                                                                                                                                                                                                                                                   "id" => null 
                                                                                                                                                                                                                                                ] 
                                                                                                                                                                                                                                          ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                      "id" => 13, 
                                                                                                                                                                                                                                                      "number" => 2, 
                                                                                                                                                                                                                                                      "stage_id" => 0, 
                                                                                                                                                                                                                                                      "group_id" => 0, 
                                                                                                                                                                                                                                                      "round_id" => 2, 
                                                                                                                                                                                                                                                      "child_count" => 0, 
                                                                                                                                                                                                                                                      "status" => 0, 
                                                                                                                                                                                                                                                      "opponent1" => [
                                                                                                                                                                                                                                                         "id" => null 
                                                                                                                                                                                                                                                      ], 
                                                                                                                                                                                                                                                      "opponent2" => [
                                                                                                                                                                                                                                                            "id" => null 
                                                                                                                                                                                                                                                         ] 
                                                                                                                                                                                                                                                   ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                               "id" => 14, 
                                                                                                                                                                                                                                                               "number" => 1, 
                                                                                                                                                                                                                                                               "stage_id" => 0, 
                                                                                                                                                                                                                                                               "group_id" => 0, 
                                                                                                                                                                                                                                                               "round_id" => 3, 
                                                                                                                                                                                                                                                               "child_count" => 0, 
                                                                                                                                                                                                                                                               "status" => 0, 
                                                                                                                                                                                                                                                               "opponent1" => [
                                                                                                                                                                                                                                                                  "id" => null 
                                                                                                                                                                                                                                                               ], 
                                                                                                                                                                                                                                                               "opponent2" => [
                                                                                                                                                                                                                                                                     "id" => null 
                                                                                                                                                                                                                                                                  ] 
                                                                                                                                                                                                                                                            ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                        "id" => 15, 
                                                                                                                                                                                                                                                                        "number" => 1, 
                                                                                                                                                                                                                                                                        "stage_id" => 0, 
                                                                                                                                                                                                                                                                        "group_id" => 1, 
                                                                                                                                                                                                                                                                        "round_id" => 4, 
                                                                                                                                                                                                                                                                        "child_count" => 0, 
                                                                                                                                                                                                                                                                        "status" => 1, 
                                                                                                                                                                                                                                                                        "opponent1" => [
                                                                                                                                                                                                                                                                           "id" => 1, 
                                                                                                                                                                                                                                                                           "position" => 1 
                                                                                                                                                                                                                                                                        ], 
                                                                                                                                                                                                                                                                        "opponent2" => [
                                                                                                                                                                                                                                                                              "id" => null, 
                                                                                                                                                                                                                                                                              "position" => 2 
                                                                                                                                                                                                                                                                           ] 
                                                                                                                                                                                                                                                                     ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                 "id" => 16, 
                                                                                                                                                                                                                                                                                 "number" => 2, 
                                                                                                                                                                                                                                                                                 "stage_id" => 0, 
                                                                                                                                                                                                                                                                                 "group_id" => 1, 
                                                                                                                                                                                                                                                                                 "round_id" => 4, 
                                                                                                                                                                                                                                                                                 "child_count" => 0, 
                                                                                                                                                                                                                                                                                 "status" => 0, 
                                                                                                                                                                                                                                                                                 "opponent1" => null, 
                                                                                                                                                                                                                                                                                 "opponent2" => [
                                                                                                                                                                                                                                                                                    "id" => null, 
                                                                                                                                                                                                                                                                                    "position" => 4 
                                                                                                                                                                                                                                                                                 ] 
                                                                                                                                                                                                                                                                              ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                       "id" => 17, 
                                                                                                                                                                                                                                                                                       "number" => 3, 
                                                                                                                                                                                                                                                                                       "stage_id" => 0, 
                                                                                                                                                                                                                                                                                       "group_id" => 1, 
                                                                                                                                                                                                                                                                                       "round_id" => 4, 
                                                                                                                                                                                                                                                                                       "child_count" => 0, 
                                                                                                                                                                                                                                                                                       "status" => 0, 
                                                                                                                                                                                                                                                                                       "opponent1" => [
                                                                                                                                                                                                                                                                                          "id" => null, 
                                                                                                                                                                                                                                                                                          "position" => 5 
                                                                                                                                                                                                                                                                                       ], 
                                                                                                                                                                                                                                                                                       "opponent2" => [
                                                                                                                                                                                                                                                                                             "id" => null, 
                                                                                                                                                                                                                                                                                             "position" => 6 
                                                                                                                                                                                                                                                                                          ] 
                                                                                                                                                                                                                                                                                    ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                "id" => 18, 
                                                                                                                                                                                                                                                                                                "number" => 4, 
                                                                                                                                                                                                                                                                                                "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                "group_id" => 1, 
                                                                                                                                                                                                                                                                                                "round_id" => 4, 
                                                                                                                                                                                                                                                                                                "child_count" => 0, 
                                                                                                                                                                                                                                                                                                "status" => 0, 
                                                                                                                                                                                                                                                                                                "opponent1" => [
                                                                                                                                                                                                                                                                                                   "id" => null, 
                                                                                                                                                                                                                                                                                                   "position" => 7 
                                                                                                                                                                                                                                                                                                ], 
                                                                                                                                                                                                                                                                                                "opponent2" => [
                                                                                                                                                                                                                                                                                                      "id" => null, 
                                                                                                                                                                                                                                                                                                      "position" => 8 
                                                                                                                                                                                                                                                                                                   ] 
                                                                                                                                                                                                                                                                                             ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                         "id" => 19, 
                                                                                                                                                                                                                                                                                                         "number" => 1, 
                                                                                                                                                                                                                                                                                                         "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                         "group_id" => 1, 
                                                                                                                                                                                                                                                                                                         "round_id" => 5, 
                                                                                                                                                                                                                                                                                                         "child_count" => 0, 
                                                                                                                                                                                                                                                                                                         "status" => 0, 
                                                                                                                                                                                                                                                                                                         "opponent1" => [
                                                                                                                                                                                                                                                                                                            "id" => null, 
                                                                                                                                                                                                                                                                                                            "position" => 2 
                                                                                                                                                                                                                                                                                                         ], 
                                                                                                                                                                                                                                                                                                         "opponent2" => [
                                                                                                                                                                                                                                                                                                               "id" => null 
                                                                                                                                                                                                                                                                                                            ] 
                                                                                                                                                                                                                                                                                                      ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                  "id" => 20, 
                                                                                                                                                                                                                                                                                                                  "number" => 2, 
                                                                                                                                                                                                                                                                                                                  "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                  "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                  "round_id" => 5, 
                                                                                                                                                                                                                                                                                                                  "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                  "status" => 0, 
                                                                                                                                                                                                                                                                                                                  "opponent1" => [
                                                                                                                                                                                                                                                                                                                     "id" => null, 
                                                                                                                                                                                                                                                                                                                     "position" => 1 
                                                                                                                                                                                                                                                                                                                  ], 
                                                                                                                                                                                                                                                                                                                  "opponent2" => [
                                                                                                                                                                                                                                                                                                                        "id" => null 
                                                                                                                                                                                                                                                                                                                     ] 
                                                                                                                                                                                                                                                                                                               ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                           "id" => 21, 
                                                                                                                                                                                                                                                                                                                           "number" => 3, 
                                                                                                                                                                                                                                                                                                                           "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                           "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                           "round_id" => 5, 
                                                                                                                                                                                                                                                                                                                           "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                           "status" => 0, 
                                                                                                                                                                                                                                                                                                                           "opponent1" => [
                                                                                                                                                                                                                                                                                                                              "id" => null, 
                                                                                                                                                                                                                                                                                                                              "position" => 4 
                                                                                                                                                                                                                                                                                                                           ], 
                                                                                                                                                                                                                                                                                                                           "opponent2" => [
                                                                                                                                                                                                                                                                                                                                 "id" => null 
                                                                                                                                                                                                                                                                                                                              ] 
                                                                                                                                                                                                                                                                                                                        ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                    "id" => 22, 
                                                                                                                                                                                                                                                                                                                                    "number" => 4, 
                                                                                                                                                                                                                                                                                                                                    "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                    "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                                    "round_id" => 5, 
                                                                                                                                                                                                                                                                                                                                    "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                    "status" => 0, 
                                                                                                                                                                                                                                                                                                                                    "opponent1" => [
                                                                                                                                                                                                                                                                                                                                       "id" => null, 
                                                                                                                                                                                                                                                                                                                                       "position" => 3 
                                                                                                                                                                                                                                                                                                                                    ], 
                                                                                                                                                                                                                                                                                                                                    "opponent2" => [
                                                                                                                                                                                                                                                                                                                                          "id" => null 
                                                                                                                                                                                                                                                                                                                                       ] 
                                                                                                                                                                                                                                                                                                                                 ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                             "id" => 23, 
                                                                                                                                                                                                                                                                                                                                             "number" => 1, 
                                                                                                                                                                                                                                                                                                                                             "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                             "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                                             "round_id" => 6, 
                                                                                                                                                                                                                                                                                                                                             "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                             "status" => 0, 
                                                                                                                                                                                                                                                                                                                                             "opponent1" => [
                                                                                                                                                                                                                                                                                                                                                "id" => null 
                                                                                                                                                                                                                                                                                                                                             ], 
                                                                                                                                                                                                                                                                                                                                             "opponent2" => [
                                                                                                                                                                                                                                                                                                                                                   "id" => null 
                                                                                                                                                                                                                                                                                                                                                ] 
                                                                                                                                                                                                                                                                                                                                          ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                                      "id" => 24, 
                                                                                                                                                                                                                                                                                                                                                      "number" => 2, 
                                                                                                                                                                                                                                                                                                                                                      "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                                      "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                                                      "round_id" => 6, 
                                                                                                                                                                                                                                                                                                                                                      "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                                      "status" => 0, 
                                                                                                                                                                                                                                                                                                                                                      "opponent1" => [
                                                                                                                                                                                                                                                                                                                                                         "id" => null 
                                                                                                                                                                                                                                                                                                                                                      ], 
                                                                                                                                                                                                                                                                                                                                                      "opponent2" => [
                                                                                                                                                                                                                                                                                                                                                            "id" => null 
                                                                                                                                                                                                                                                                                                                                                         ] 
                                                                                                                                                                                                                                                                                                                                                   ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                                               "id" => 25, 
                                                                                                                                                                                                                                                                                                                                                               "number" => 1, 
                                                                                                                                                                                                                                                                                                                                                               "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                                               "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                                                               "round_id" => 7, 
                                                                                                                                                                                                                                                                                                                                                               "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                                               "status" => 0, 
                                                                                                                                                                                                                                                                                                                                                               "opponent1" => [
                                                                                                                                                                                                                                                                                                                                                                  "id" => null, 
                                                                                                                                                                                                                                                                                                                                                                  "position" => 2 
                                                                                                                                                                                                                                                                                                                                                               ], 
                                                                                                                                                                                                                                                                                                                                                               "opponent2" => [
                                                                                                                                                                                                                                                                                                                                                                     "id" => null 
                                                                                                                                                                                                                                                                                                                                                                  ] 
                                                                                                                                                                                                                                                                                                                                                            ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                                                        "id" => 26, 
                                                                                                                                                                                                                                                                                                                                                                        "number" => 2, 
                                                                                                                                                                                                                                                                                                                                                                        "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                                                        "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                                                                        "round_id" => 7, 
                                                                                                                                                                                                                                                                                                                                                                        "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                                                        "status" => 0, 
                                                                                                                                                                                                                                                                                                                                                                        "opponent1" => [
                                                                                                                                                                                                                                                                                                                                                                           "id" => null, 
                                                                                                                                                                                                                                                                                                                                                                           "position" => 1 
                                                                                                                                                                                                                                                                                                                                                                        ], 
                                                                                                                                                                                                                                                                                                                                                                        "opponent2" => [
                                                                                                                                                                                                                                                                                                                                                                              "id" => null 
                                                                                                                                                                                                                                                                                                                                                                           ] 
                                                                                                                                                                                                                                                                                                                                                                     ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                                                                 "id" => 27, 
                                                                                                                                                                                                                                                                                                                                                                                 "number" => 1, 
                                                                                                                                                                                                                                                                                                                                                                                 "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                 "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                                                                                 "round_id" => 8, 
                                                                                                                                                                                                                                                                                                                                                                                 "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                 "status" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                 "opponent1" => [
                                                                                                                                                                                                                                                                                                                                                                                    "id" => null 
                                                                                                                                                                                                                                                                                                                                                                                 ], 
                                                                                                                                                                                                                                                                                                                                                                                 "opponent2" => [
                                                                                                                                                                                                                                                                                                                                                                                       "id" => null 
                                                                                                                                                                                                                                                                                                                                                                                    ] 
                                                                                                                                                                                                                                                                                                                                                                              ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                                                                          "id" => 28, 
                                                                                                                                                                                                                                                                                                                                                                                          "number" => 1, 
                                                                                                                                                                                                                                                                                                                                                                                          "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                          "group_id" => 1, 
                                                                                                                                                                                                                                                                                                                                                                                          "round_id" => 9, 
                                                                                                                                                                                                                                                                                                                                                                                          "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                          "status" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                          "opponent1" => [
                                                                                                                                                                                                                                                                                                                                                                                             "id" => null, 
                                                                                                                                                                                                                                                                                                                                                                                             "position" => 1 
                                                                                                                                                                                                                                                                                                                                                                                          ], 
                                                                                                                                                                                                                                                                                                                                                                                          "opponent2" => [
                                                                                                                                                                                                                                                                                                                                                                                                "id" => null 
                                                                                                                                                                                                                                                                                                                                                                                             ] 
                                                                                                                                                                                                                                                                                                                                                                                       ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                                                                                   "id" => 29, 
                                                                                                                                                                                                                                                                                                                                                                                                   "number" => 1, 
                                                                                                                                                                                                                                                                                                                                                                                                   "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                                   "group_id" => 2, 
                                                                                                                                                                                                                                                                                                                                                                                                   "round_id" => 10, 
                                                                                                                                                                                                                                                                                                                                                                                                   "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                                   "status" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                                   "opponent1" => [
                                                                                                                                                                                                                                                                                                                                                                                                      "id" => null 
                                                                                                                                                                                                                                                                                                                                                                                                   ], 
                                                                                                                                                                                                                                                                                                                                                                                                   "opponent2" => [
                                                                                                                                                                                                                                                                                                                                                                                                         "id" => null, 
                                                                                                                                                                                                                                                                                                                                                                                                         "position" => 1 
                                                                                                                                                                                                                                                                                                                                                                                                      ] 
                                                                                                                                                                                                                                                                                                                                                                                                ], 
                                                                                                                                 [
                                                                                                                                                                                                                                                                                                                                                                                                            "id" => 30, 
                                                                                                                                                                                                                                                                                                                                                                                                            "number" => 1, 
                                                                                                                                                                                                                                                                                                                                                                                                            "stage_id" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                                            "group_id" => 2, 
                                                                                                                                                                                                                                                                                                                                                                                                            "round_id" => 11, 
                                                                                                                                                                                                                                                                                                                                                                                                            "child_count" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                                            "status" => 0, 
                                                                                                                                                                                                                                                                                                                                                                                                            "opponent1" => [
                                                                                                                                                                                                                                                                                                                                                                                                               "id" => null 
                                                                                                                                                                                                                                                                                                                                                                                                            ], 
                                                                                                                                                                                                                                                                                                                                                                                                            "opponent2" => [
                                                                                                                                                                                                                                                                                                                                                                                                                  "id" => null 
                                                                                                                                                                                                                                                                                                                                                                                                               ] 
                                                                                                                                                                                                                                                                                                                                                                                                         ] 
                                                                                                                              ], 
            "match_game" => [
                                                                                                                                                                                                                                                                                                                                                                                                                  ] 
        ];

        return $response->setData($bracketData)->send();
    }

    public function generateBracket($tournamentId)
    {
        $response = new APIResponse();
        $user = User::getUserById($_SESSION['user_id']);
        $tournament = Tournament::getById($tournamentId);

        // Add actual bracket generation logic here
        // This would typically create matches between registered teams

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

        // Add user to team logic
        Tournament::addParticipant($team['tournament_team_id'], $_SESSION['user_id']);

        return $response->setData(['message' => 'Joined team successfully'])->send();
    }
}