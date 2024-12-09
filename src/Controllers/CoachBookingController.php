<?php

namespace Controllers;

use Core\View;
use Models\CoachBooking;
use Models\User;
use Models\Team;

class CoachBookingController
{
    public function index()
    {
        $coachBookings = CoachBooking::getAllBookings();
        echo View::render('/dashboard/coaches/index', ['coachBookings' => $coachBookings]);
    }

    public function create()
    {
        $coaches = CoachBooking::getAllCoaches();
        $teams = Team::getAll();
        echo View::render('/dashboard/coaches/create', ['coaches' => $coaches, 'teams' => $teams]);
    }

    public function store()
    {
        $coach_id = $_POST['coach_id'];
        $date = $_POST['reservation_date'];
        $time = $_POST['reservation_time'];
        $user_id = $_POST['user_id'] ?? null;
        $team_id = $_POST['team_id'] ?? null; 

        CoachBooking::createBooking($coach_id, $date, $time, $user_id, $team_id);
        header('Location: /dashboard/coaches'); 
        exit();
    }

    public function delete($booking_id)
    {
        CoachBooking::deleteBooking($booking_id);
        header('Location: /dashboard/coaches');
        exit();
    }
}
