<?php

namespace Models;

use Core\Database;

class Team
{
    public static function create($event_id, $team_name)
    {
        $sql = "INSERT INTO TEAM (event_id, team_name) VALUES (:event_id, :team_name)";
        $params = [
            ':event_id' => $event_id,
            ':team_name' => $team_name
        ];
        Database::query($sql, $params);

        return Database::getConnection()->lastInsertId(); 
    }

    public static function findById($team_id)
    {
        $sql = "SELECT * FROM TEAM WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        return Database::query($sql, $params)->fetch();
    }

    public static function update($team_id, $team_name)
    {
        $sql = "UPDATE TEAM SET team_name = :team_name WHERE team_id = :team_id";
        $params = [
            ':team_name' => $team_name,
            ':team_id' => $team_id
        ];
        Database::query($sql, $params);
    }

    public static function delete($team_id)
    {
        $sql = "DELETE FROM TEAM WHERE team_id = :team_id";
        $params = [':team_id' => $team_id];
        Database::query($sql, $params);
    }
}