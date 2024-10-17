<?php

namespace Controllers;

use Core\View;
use Models\Team;
use Models\TeamParticipant;
use Models\Member;

class TeamController
{
    private $view;

    public function __construct()
    {
        $this->view = new View();
    }

    public function create($event_id)
    {
        $members = Member::getAll();
        $this->view->render('teams/create', ['event_id' => $event_id, 'members' => $members]);
    }

   
    public function store($event_id)
    {
        $teamName = $_POST['team_name'];
        $selectedMembers = $_POST['members'];

        $team = new Team();
        $team_id = $team->create($event_id, $teamName);

        foreach ($selectedMembers as $member_id) {
            $teamParticipant = new TeamParticipant();
            $teamParticipant->addParticipant($team_id, $member_id);
        }

        header('Location: /events/' . $event_id); 
        exit();
    }

    public function edit($team_id)
    {
        $team = Team::findById($team_id);
        $membersInTeam = TeamParticipant::getMembersByTeam($team_id);
        $allMembers = Member::getAll(); 

        $this->view->render('teams/edit', [
            'team' => $team,
            'membersInTeam' => $membersInTeam,
            'allMembers' => $allMembers
        ]);
    }

    public function update($team_id)
    {
        $teamName = $_POST['team_name'];
        $selectedMembers = $_POST['members'];

        $team = new Team();
        $team->update($team_id, $teamName);

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
