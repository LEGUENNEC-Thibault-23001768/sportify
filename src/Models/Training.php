<?php

namespace Models;

use Core\Database;
use Models\User;
use PDO;

class Training {

    public static function buildPrompt($data) {
        $gender = $data['gender'] ?? 'Non spécifié';
        $level = $data['level'] ?? 'Non spécifié';
        $goals = $data['goals'] ?? 'Non spécifié';
        $weight = $data['weight'] ?? 'Non spécifié';
        $height = $data['height'] ?? 'Non spécifié';
        $constraints = $data['constraints'] ?? 'Aucune contrainte signalée';
        $preferences = $data['preferences'] ?? 'Pas de préférences spécifiques';
        $equipment = $data['equipment'] ?? 'Aucun équipement disponible';
    
        return "Je suis un entraîneur personnel virtuel. Voici le profil d'un utilisateur :
            - Sexe : $gender
            - Niveau : $level
            - Objectifs : $goals
            - Poids : $weight kg
            - Taille : $height cm
            - Contraintes : $constraints
            - Préférences : $preferences
            - Équipement disponible : $equipment.
        
            Sur la base de ces informations, crée un plan d'entraînement structuré pour les 7 prochains jours. La sortie doit respecter ce format fixe :
        
            **Jour 1 :**
            - Nom de l'exercice 1 : description, durée, intensité
            - Nom de l'exercice 2 : description, durée, intensité
            - [ajouter d'autres exercices si nécessaire]
        
            **Jour 2 :**
            - [répéter le même format]
        
            Continue pour tous les jours jusqu'à **Jour 7**.
        
            Inclure des conseils généraux pour l'échauffement, la récupération et la motivation. Assurez-vous que le plan est progressif et adapté à l'utilisateur.

            ULTRA IMPORTANT: Ne pas inclure d'exercices qui pourraient aggraver les contraintes signalées.

            ULTRA IMPORTANT: Ne pas inclure de conseils généraux, ni médicaux.

            ULTRA IMPORTANT: Ne pas inclure d'adaptations pour les équipements manquants, ainsi que d'adaptations pour les contraintes signalées.

            ULTRA IMPORTANT: Si l'utilisateur signale une contrainte qui n'est pas en accord avec tes choix en tant qu'IA, créer un plan alternatif.

            Assurez-vous que le plan est progressif et adapté à l'utilisateur.";
    
    }
    


    public static function getTrainingPlan($memberId) {
        $sql = "
        SELECT tp.plan_id, da.day, da.activity_description, da.completed
        FROM TRAINING_PLAN tp
        JOIN DAILY_ACTIVITY da ON tp.plan_id = da.plan_id
        WHERE tp.member_id = :memberId
        ORDER BY da.day
        ";
        $params = [':memberId' => $memberId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public function markActivityAsCompleted($activityId) {
        $sql = "
            UPDATE DAILY_ACTIVITY
            SET completed = TRUE
            WHERE activity_id = :activityId
        ";
        $params = [':activityId' => $activityId];
        return Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

   public static function saveTrainingPlan($memberId, $data) {
    $sql = "
        INSERT INTO TRAINING_PLAN 
        (member_id, gender, level, goals, weight, height, constraints, preferences, equipment, plan_content) 
        VALUES (:memberId, :gender, :level, :goals, :weight, :height, :constraints, :preferences, :equipment, :planContent)
    ";
    $params = [
        ':memberId' => $memberId,
        ':gender' => $data['gender'],
        ':level' => $data['level'],
        ':goals' => $data['goals'],
        ':weight' => $data['weight'],
        ':height' => $data['height'],
        ':constraints' => $data['constraints'],
        ':preferences' => $data['preferences'],
        ':equipment' => $data['equipment'],
        ':planContent' => $data['planContent'],
    ];
    Database::query($sql, $params);
}

    
    
    
    public static function getExistingTrainingPlan($memberId) {
        $sql = "
            SELECT plan_content 
            FROM TRAINING_PLAN
            WHERE member_id = :memberId
            ORDER BY created_at DESC
            LIMIT 1
        ";
        $params = [':memberId' => $memberId];
        $result = Database::query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    
        return $result ?: null;
    }
    
    
    
    public static function updateTrainingPlan($memberId, $data) {
        $sql = "
            UPDATE TRAINING_PLAN
            SET 
                gender = :gender,
                level = :level,
                goals = :goals,
                weight = :weight,
                height = :height,
                constraints = :constraints,
                preferences = :preferences,
                equipment = :equipment,
                plan_content = :planContent
            WHERE member_id = :memberId
        ";
        $params = [
            ':gender' => $data['gender'],
            ':level' => $data['level'],
            ':goals' => $data['goals'],
            ':weight' => $data['weight'],
            ':height' => $data['height'],
            ':constraints' => $data['constraints'],
            ':preferences' => $data['preferences'],
            ':equipment' => $data['equipment'],
            ':planContent' => $data['planContent'],
            ':memberId' => $memberId,
        ];
    
        Database::query($sql, $params);
    }
    
    
   
}