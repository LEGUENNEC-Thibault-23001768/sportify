<?php

namespace Controllers;

use Core\View;
use Core\Config;
use Core\APIResponse;
use Models\User;
use Gemini;
use Models\Training;


class TrainingController {

    private $apiKey;

    public function __construct() {
        $this->apiKey = Config::get("gemini_key");
    }

    public function start() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    
        $memberId = $_SESSION['user_id'];
        $existingPlan = Training::getExistingTrainingPlan($memberId);
    
        if ($existingPlan) {
            header('Location: /dashboard/training');
            exit;
        }
    
        $_SESSION['training_data'] = [];
        echo View::render('dashboard/training/start', [
            'member' => User::getUserById($memberId)
        ]);
    }

    public function apiGenerate() {
        $response = new APIResponse();
        $currentUserId = $_SESSION['user_id'];
        $member = User::getUserById($currentUserId);
    
        $userInput = $_SESSION['training_data'] ?? [];
        if (empty($userInput)) {
            return $response->setStatusCode(400)->setData(['error' => 'No training data found'])->send();
        }
    
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
        $prompt = Training::buildPrompt($data, $member);
    
        try {
            $result = $client->geminiPro()->generateContent($prompt);
            $generatedText = $result->text();
    
            $jsonStart = strpos($generatedText, '{');
            $jsonEnd = strrpos($generatedText, '}');
    
            if ($jsonStart === false || $jsonEnd === false) {
                throw new \Exception("Valid JSON not found in AI response.");
            }
    
            $jsonString = substr($generatedText, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decodedPlan = json_decode($jsonString, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Failed to decode AI response: " . json_last_error_msg());
            }
    
            if (isset($decodedPlan['days']) && is_array($decodedPlan['days'])) {
                $data['planContent'] = json_encode($decodedPlan);
                Training::saveTrainingPlan($member['member_id'], $data);
                unset($_SESSION['training_data']);
    
                return $response->setStatusCode(200)->setData(['plan' => $data['planContent']])->send();
            } else {
                throw new \Exception("Unexpected AI response format.");
            }
        } catch (\Exception $e) {
            error_log("Error in training plan generation: " . $e->getMessage());
            return $response->setStatusCode(500)->setData(['error' => 'Failed to generate training plan. Please try again later.'])->send();
        }
    }
    

    public function apiProcessStep() {
        $response = new APIResponse();
    
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->setStatusCode(401)->setData(['error' => 'Unauthorized'])->send();
        }
    
        $step = $_POST['step'] ?? null;
        $data = $_POST['data'] ?? null;
    
        if ($step === null || $data === null) {
            return $response->setStatusCode(400)->setData(['error' => 'Invalid data provided'])->send();
        }
    
        $_SESSION['training_data'][$step] = $data;
    
        if ($step < 8) {
            return $response->setStatusCode(200)->setData(['next_step' => $step + 1])->send();
        } else {
            return $response->setStatusCode(200)->setData(['next_step' => 'generate'])->send();
        }
    }
    

    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    
        $memberId = $_SESSION['user_id'];
        $existingPlan = Training::getExistingTrainingPlan($memberId);
    
        if ($existingPlan) {
            echo View::render('dashboard/training/result', [
                'plan' => $existingPlan['plan_content'],
            ]);
        } else {
            header('Location: /dashboard/training/start');
            exit;
        }
    }

    public function train() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    
        $memberId = $_SESSION['user_id'];
        $existingPlan = Training::getExistingTrainingPlan($memberId);
    
        if ($existingPlan) {
            $planData = json_decode($existingPlan['plan_content'], true);
            $day = $_GET['day'] ?? 'Monday'; // Get the day from the query parameter
    
            // Find the data for the selected day
            $dayContent = null;
            foreach ($planData['days'] as $d) {
                if ($d['day'] === $day) {
                    $dayContent = $d;
                    break;
                }
            }
    
            if ($dayContent) {
                echo View::render('dashboard/training/train', [
                    'dayContent' => $dayContent,
                ]);
            } else {
                echo "Day not found in the training plan.";
            }
        } else {
            header('Location: /dashboard/training/start');
            exit;
        }
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
