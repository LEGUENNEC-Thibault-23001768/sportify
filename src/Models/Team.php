<?php

namespace Models;

use Core\Database;

class Team
{

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    // Créer une nouvelle équipe
    public function create($event_id, $team_name)
    {
        $stmt = $this->db->prepare("INSERT INTO TEAM (event_id, team_name) VALUES (:event_id, :team_name)");
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':team_name', $team_name);
        $stmt->execute();

        return $this->db->lastInsertId(); // Retourner l'ID de la nouvelle équipe
    }

    // Trouver une équipe par son ID
    public static function findById($team_id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM TEAM WHERE team_id = :team_id");
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Mettre à jour le nom de l'équipe
    public function update($team_id, $team_name)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE TEAM SET team_name = :team_name WHERE team_id = :team_id");
        $stmt->bindParam(':team_name', $team_name);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
    }

    // Supprimer une équipe
    public static function delete($team_id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM TEAM WHERE team_id = :team_id");
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
    }
}
