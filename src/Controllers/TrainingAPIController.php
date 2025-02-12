<?php
namespace Controllers;

use Core\APIController;
use Core\APIResponse;
use Models\User;
use Models\Training;
use Gemini;
use Core\Config;
use Core\Router;
use Core\RouteProvider;
use Core\Auth;

class TrainingAPIController extends APIController implements RouteProvider {
    private $apiKey;

    public static function routes() : void
    {
        Router::get('/api/training', self::class . '@get', Auth::requireLogin());
        Router::post('/api/training/process-step', self::class . '@processStep', Auth::requireLogin());
        Router::post('/api/training/generate', self::class . '@generate', Auth::requireLogin());
        Router::post('/api/training/update', self::class . '@update', Auth::requireLogin());
    }


    public function __construct() {
        $this->apiKey = Config::get("gemini_key");
    }
    public function processStep() {
         $response = new APIResponse();
     
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

    public function generate() {
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
         $prompt = Training::buildPrompt($data);
     
         try {
            $result = $client->geminiPro()->generateContent($prompt);
            $generatedText = $result->text();
    
            $jsonStart = strpos($generatedText, '{');
            $jsonEnd = strrpos($generatedText, '}');
    
            if ($jsonStart === false || $jsonEnd === false) {
                 error_log("Valid JSON not found in AI response.");
                   return $response->setStatusCode(500)->setData(['error' => 'Failed to generate training plan. Please try again later.'])->send();

            }
    
            $jsonString = substr($generatedText, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decodedPlan = json_decode($jsonString, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log(print_r($jsonString,true));
                error_log("Failed to decode AI response: " . json_last_error_msg());
                return $response->setStatusCode(500)->setData(['error' => 'Failed to generate training plan. Please try again later.'])->send();
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

    public function update() {
         $response = new APIResponse();
         $memberId = $_SESSION['user_id'] ?? null;
     
         if (!$memberId) {
             return $response->setStatusCode(401)->setData(['error' => 'User not authenticated'])->send();
         }
     
         $existingPlan = Training::getExistingTrainingPlan($memberId);
     
         if(!$existingPlan){
              return $response->setStatusCode(404)->setData(['error' => 'No training plan found'])->send();
         }

         $inputs = $_POST;
 
         // Utiliser les nouvelles valeurs ou conserver les anciennes
         $updatedData = [
             'gender' => $inputs['gender'] ?? $existingPlan['gender'] ?? null,
             'level' => $inputs['level'] ?? $existingPlan['level'] ?? null,
             'goals' => $inputs['goals'] ?? $existingPlan['goals'] ?? null,
             'weight' => $inputs['weight'] ?? $existingPlan['weight'] ?? null,
             'height' => $inputs['height'] ?? $existingPlan['height'] ?? null,
             'constraints' => $inputs['constraints'] ?? $existingPlan['constraints'] ?? null,
             'preferences' => $inputs['preferences'] ?? $existingPlan['preferences'] ?? null,
             'equipment' => $inputs['equipment'] ?? $existingPlan['equipment'] ?? null,
         ];
 
         $prompt = Training::buildPrompt($updatedData);
         $client = Gemini::client($this->apiKey);
         $result = $client->geminiPro()->generateContent($prompt);
         $generatedText = $result->text();

        $jsonStart = strpos($generatedText, '{');
        $jsonEnd = strrpos($generatedText, '}');

        if ($jsonStart === false || $jsonEnd === false) {
             error_log("Valid JSON not found in AI response.");
             return $response->setStatusCode(500)->setData(['error' => 'Failed to update training plan. Please try again later.'])->send();
        }

        $jsonString = substr($generatedText, $jsonStart, $jsonEnd - $jsonStart + 1);
        $decodedPlan = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log(print_r($jsonString,true));
            error_log("Failed to decode AI response: " . json_last_error_msg());
            return $response->setStatusCode(500)->setData(['error' => 'Failed to update training plan. Please try again later.'])->send();
        }
    
         if (isset($decodedPlan['days']) && is_array($decodedPlan['days'])) {
             $updatedData['planContent'] = json_encode($decodedPlan);
             Training::updateTrainingPlan($memberId, $updatedData);
             return $response->setStatusCode(200)->setData(['message' => 'Training plan updated'])->send();
          } else {
               throw new \Exception("Unexpected AI response format.");
         }
     }
    
    public function get($id=null){
        $response = new APIResponse();
        $memberId = $_SESSION['user_id'];
        $existingPlan = Training::getExistingTrainingPlan($memberId);
        
        if($existingPlan) {
           return $response->setStatusCode(200)->setData(['plan' => $existingPlan['plan_content']])->send();
       } else {
            return $response->setStatusCode(404)->setData(['message' => 'No training plan found'])->send();
       }
   }
}