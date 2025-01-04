<?php

namespace Models;

use Core\Database;

class Team
{
    // Create a new team
    public static function create($event_id, $team_name)
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
    public static function findById($team_id)
    {
        $sql = "SELECT * FROM TEAM WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        return Database::query($sql, $params)->fetch();
    }

    // Update the team name
    public static function update($team_id, $team_name)
    {
        $sql = "UPDATE TEAM SET team_name = :team_name WHERE team_id = :team_id";
        $params = [
            ':team_name' => $team_name,
            ':team_id' => $team_id
        ];
        Database::query($sql, $params);
    }

    // Delete a team
    public static function delete($team_id)
    {
        $sql = "DELETE FROM TEAM WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        Database::query($sql, $params);
    }

    public static function getAll()
    {
        // Requête SQL pour récupérer toutes les équipes
        $sql = "SELECT * FROM TEAM";

        // Exécuter la requête
        $result = Database::query($sql);

        // Retourner toutes les équipes sous forme de tableau
        return $result->fetchAll();
    }

}