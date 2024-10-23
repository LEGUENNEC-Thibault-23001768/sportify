<?php

namespace Controllers;

use Core\View;
use Models\User;
use Models\Team;
use Models\TeamParticipant;

class TeamController
{
    public function create($event_id)
    {
        $members = User::getAll();
        echo View::render('teams/create', ['event_id' => $event_id, 'members' => $members]);
    }

   
    public function store($event_id)
    {
        $teamName = $_POST['team_name'];
        $selectedMembers = $_POST['members'];

        $team_id = Team::create($event_id, $teamName);

        foreach ($selectedMembers as $member_id) {
            TeamParticipant::addParticipant($team_id, $member_id);
        }

        header('Location: /events/' . $event_id); 
        exit();
    }

    public function edit($team_id)
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

    public function update($team_id)
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
        exit();
    }

    public function delete($team_id)
    {
        TeamParticipant::deleteParticipantsByTeam($team_id);

        Team::delete($team_id);

        header('Location: /teams');
        exit();
    }
}
