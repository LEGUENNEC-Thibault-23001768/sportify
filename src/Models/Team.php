<?php

namespace Models;

use Core\Database;

class Team
{
    // Create a new team
    /**
     * @param int $event_id
     * @param string $team_name
     * @return false|string
     */
    public static function create(int $event_id, string $team_name): false|string
    {
        $sql = "INSERT INTO TEAM (event_id, team_name) VALUES (:event_id, :team_name)";
        $params = [
            ':event_id' => $event_id,
            ':team_name' => $team_name
        ];
        Database::query($sql, $params);

        return Database::getConnection()->lastInsertId(); // Return the ID of the new team
    }

    // Find a team by its ID

    /**
     * @param int $team_id
     * @return mixed
     */
    public static function findById(int $team_id): mixed
    {
        $sql = "SELECT * FROM TEAM WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        return Database::query($sql, $params)->fetch();
    }

    // Update the team name

    /**
     * @param int $team_id
     * @param string $team_name
     * @return void
     */
    public static function update(int $team_id, string $team_name): void
    {
        $sql = "UPDATE TEAM SET team_name = :team_name WHERE team_id = :team_id";
        $params = [
            ':team_name' => $team_name,
            ':team_id' => $team_id
        ];
        Database::query($sql, $params);
    }

    // Delete a team

    /**
     * @param int $team_id
     * @return void
     */
    public static function delete(int $team_id): void
    {
        $sql = "DELETE FROM TEAM WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        Database::query($sql, $params);
    }
}