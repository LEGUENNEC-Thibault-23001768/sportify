<?php

namespace Controllers;

use Core\View;
use Exception;
use Models\CoachBooking;
use Models\Team;

class CoachBookingController
{
    /**
     * @return void
     * @throws Exception
     */
    public function index(): void
    {
        $coachBookings = CoachBooking::getAllBookings();
        echo View::render('/dashboard/coaches/index', ['coachBookings' => $coachBookings]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function create(): void
    {
        $coaches = CoachBooking::getAllCoaches();
        $teams = Team::getAll();
        echo View::render('/dashboard/coaches/create', ['coaches' => $coaches, 'teams' => $teams]);
    }

    /**
     * @return void
     */
    public function store(): void
    {
        $coach_id = $_POST['coach_id'];
        $date = $_POST['reservation_date'];
        $time = $_POST['reservation_time'];
        $user_id = $_POST['user_id'] ?? null;
        $team_id = $_POST['team_id'] ?? null;

        CoachBooking::createBooking($coach_id, $date, $time, $user_id, $team_id);
        header('Location: /dashboard/coaches');
    }

    /**
     * @param $booking_id
     * @return void
     */
    public function delete($booking_id): void
    {
        CoachBooking::deleteBooking($booking_id);
        header('Location: /dashboard/coaches');
    }
}
