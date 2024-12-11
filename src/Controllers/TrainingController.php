<?php

namespace Controllers;

use Core\View;
use Gemini;
use Models\Training;

class TrainingController {

    private $apiKey = 'AIzaSyA1ddcTD8aE14BeSwgd0ceJUQtyH93YCH4';
    
    public function start() {
        session_start();
        $memberId = $_SESSION['user_id']; 
    
        $existingPlan = Training::getExistingTrainingPlan($memberId);
    
        if ($existingPlan) {
            header('Location: /dashboard/training');
            exit();
        }
    
        $_SESSION['training_data'] = [];
        header('Location: /dashboard/training/step/1');
        exit();
    }
    

    public function dashboard() {
        session_start();
        $memberId = $_SESSION['user_id']; 
    
        $existingPlan = Training::getExistingTrainingPlan($memberId);
    
        if ($existingPlan) {
            echo View::render('dashboard/training/result', [
                'plan' => $existingPlan,
            ]);
        } else {
            header('Location: /dashboard/training/start');
            exit();
        }
    }
    
    

    public function step($step) {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST['input'];
            $_SESSION['training_data'][$step] = $data;
    
            if ($step < 8) { 
                header('Location: /dashboard/training/step/' . ($step + 1));
                exit();
            } else {
                header('Location: /dashboard/training/generate');
                exit();
            }
        }
    
        echo View::render('dashboard/training/step', ['step' => $step, 'data' => $_SESSION['training_data'] ?? []]);
    }

    public function generate() {
        session_start();
        $userInput = $_SESSION['training_data'] ?? [];
        $memberId = $_SESSION['user_id'];
    
        $data = [
            'gender' => $userInput[1] ?? null,
            'level' => $userInput[2] ?? null,
            'goals' => $userInput[3] ?? null,
            'weight' => $userInput[4] ?? null,
            'height' => $userInput[5] ?? null,
            'constraints' => $userInput[6] ?? null,
            'preferences' => $userInput[7] ?? null,
            'equipment' => $userInput[8] ?? null,
        ];
    
        $client = Gemini::client($this->apiKey);
    
        $prompt = Training::buildPrompt($data);
    
        $result = $client->geminiPro()->generateContent($prompt);
        $generatedText = $result->text();
    
        $data['planContent'] = $generatedText;
        Training::saveTrainingPlan($memberId, $data);
    
        echo View::render('dashboard/training/result', [
            'plan' => $generatedText,
            'data' => $data,
        ]);
    }
    

    public function edit() {
        session_start();
        $memberId = $_SESSION['user_id'] ?? null;
    
        if (!$memberId) {
            header('Location: /login');
            exit();
        }
    
        $existingPlan = Training::getExistingTrainingPlan($memberId);
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputs = $_POST;
    
            // Utiliser les nouvelles valeurs ou conserver les anciennes
            $updatedData = [
                'gender' => $inputs['gender'] ?: ($existingPlan['gender'] ?? null),
                'level' => $inputs['level'] ?: ($existingPlan['level'] ?? null),
                'goals' => $inputs['goals'] ?: ($existingPlan['goals'] ?? null),
                'weight' => $inputs['weight'] ?: ($existingPlan['weight'] ?? null),
                'height' => $inputs['height'] ?: ($existingPlan['height'] ?? null),
                'constraints' => $inputs['constraints'] ?: ($existingPlan['constraints'] ?? null),
                'preferences' => $inputs['preferences'] ?: ($existingPlan['preferences'] ?? null),
                'equipment' => $inputs['equipment'] ?: ($existingPlan['equipment'] ?? null),
            ];
    
            // Générer un nouveau plan d'entraînement basé sur les nouvelles données
            $prompt = Training::buildPrompt($updatedData);
            $client = Gemini::client($this->apiKey);
            $result = $client->geminiPro()->generateContent($prompt);
            $generatedText = $result->text();
    
            // Mettre à jour le plan d'entraînement existant dans la base de données
            $updatedData['planContent'] = $generatedText;
            Training::updateTrainingPlan($memberId, $updatedData);
    
            header('Location: /dashboard/training');
            exit();
        }
    
        echo View::render('dashboard/training/edit', [
            'existingPlan' => $existingPlan,
        ]);
    }
    
    
    
    
    
    
    
    
}
