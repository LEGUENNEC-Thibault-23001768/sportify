<?php

namespace Controllers;

use Core\View;
use Exception;
use Models\Team;
use Models\TeamParticipant;
use Models\User;

class TeamController
{
    /**
     * @param $event_id
     * @return void
     */
    public function store($event_id): void
    {
        $teamName = $_POST['team_name'];
        $selectedMembers = $_POST['members'];

        $team_id = Team::create($event_id, $teamName);

        foreach ($selectedMembers as $member_id) {
            TeamParticipant::addParticipant($team_id, $member_id);
        }

        header('Location: /events/' . $event_id);
    }

    /**
     * @param $event_id
     * @return void
     * @throws Exception
     */
    public function create($event_id): void
    {
        $members = User::getAll();
        echo View::render('teams/create', ['event_id' => $event_id, 'members' => $members]);
    }

    /**
     * @param $team_id
     * @return void
     * @throws Exception
     */
    public function edit($team_id): void
    {
        $team = Team::findById($team_id);
        $membersInTeam = TeamParticipant::getMembersByTeam($team_id);
        $allMembers = User::getAll();

        echo View::render('teams/edit', [
            'team' => $team,
            'membersInTeam' => $membersInTeam,
            'allMembers' => $allMembers
        ]);
    }

    /**
     * @param $team_id
     * @return void
     */
    public function update($team_id): void
    {
        $teamName = $_POST['team_name'];
        $selectedMembers = $_POST['members'];

        Team::update($team_id, $teamName);

        TeamParticipant::deleteParticipantsByTeam($team_id);

        foreach ($selectedMembers as $member_id) {
            $teamParticipant = new TeamParticipant();
            $teamParticipant->addParticipant($team_id, $member_id);
        }

        header('Location: /teams/' . $team_id);
    }

    /**
     * @param $team_id
     * @return void
     */
    public function delete($team_id): void
    {
        TeamParticipant::deleteParticipantsByTeam($team_id);

        Team::delete($team_id);

        header('Location: /teams');
    }
}
