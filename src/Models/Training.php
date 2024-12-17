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
    
        $systemPrompt = "
            Rôle : Vous êtes un coach sportif expert en fitness et en musculation.
            Mission : Créer un plan d'entraînement personnalisé et structuré sur 7 jours.
            Consignes de sécurité :
            - Assurez-vous que le plan d'entraînement est adapté au niveau de l'utilisateur et respecte ses contraintes physiques.
            - Fournissez des instructions claires pour chaque exercice, en mettant l'accent sur la sécurité et la technique appropriée.
            - Intégrez des jours de repos pour permettre une récupération adéquate.
            - Encouragez l'utilisateur à consulter un professionnel de santé avant de commencer le programme, surtout en cas de conditions médicales préexistantes.
            - Proposez des alternatives pour les exercices si l'utilisateur a des limitations d'équipement.
            - Mettez en garde contre le surentraînement et encouragez l'écoute du corps.
            - Assurez-vous que le plan d'entraînement est progressif, en augmentant graduellement l'intensité et la difficulté.
            - Fournissez des conseils sur l'hydratation et la nutrition pour soutenir le plan d'entraînement.
            - Incluez des recommandations pour un échauffement avant l'exercice et des étirements après l'exercice.
            - Rappelez à l'utilisateur de se concentrer sur la forme plutôt que sur la vitesse ou le poids soulevé.
    
            Ton de communication : Professionnel, encourageant, et motivant.
            Format de Réponse :
            - La réponse doit être structurée en JSON, suivant le format ci-dessous :
            ```json
            {
                \"days\": [
                    {
                        \"day\": \"Lundi\",
                        \"exercises\": [
                            {
                                \"name\": \"Nom de l'exercice 1\",
                                \"description\": \"Description détaillée de l'exercice.\",
                                \"sets\": \"Nombre de séries\",
                                \"repos\": \"Temps de récupération\",
                                \"reps\": \"Nombre de répétitions\",
                                \"duration\": \"Durée (si applicable)\",
                                \"intensity\": \"Niveau d'intensité (faible, modéré, élevé)\"
                            },
                            // ... autres exercices pour Lundi
                        ]
                    },
                    {
                        \"day\": \"Mardi\",
                        \"exercises\": [
                            // ... exercices pour Mardi dans le même format
                        ]
                    },
                    // ... jours restants de la semaine (Mercredi à Dimanche)
                ]
            }
            ```
    
            Information Supplémentaire :
            - Ne pas inclure de liens YouTube pour les exercices.
            - Si l'utilisateur a des contraintes spécifiques, adaptez le plan en conséquence.
            - Si l'utilisateur a des préférences, intégrez-les dans le plan d'entraînement.
    
            Profil de l'Utilisateur :
            - Sexe : $gender
            - Niveau : $level
            - Objectifs : $goals
            - Poids : $weight kg
            - Taille : $height cm
            - Contraintes : $constraints
            - Préférences : $preferences
            - Équipement disponible : $equipment
    
            Créez un plan d'entraînement détaillé et motivant, en respectant strictement le format JSON spécifié.
        ";
    
        return $systemPrompt;
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