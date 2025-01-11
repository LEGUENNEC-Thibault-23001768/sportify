<?php

namespace Controllers;

use Core\APIController;
use Core\APIResponse;
use Core\Auth;
use Models\User;
use Models\Subscription;
use Core\RouteProvider;
use Core\Router;

class UserAPIController extends APIController implements RouteProvider
{
    public static function routes() : void
    {
        Router::put('/api/profile', self::class . '@updateProfile', Auth::requireLogin()); 
        Router::apiResource('/api/users', self::class, Auth::isAdmin());
        Router::get('/api/users/{user_id}/subscription', self::class . '@getSubscription', Auth::isAdmin());
        Router::post('/api/users/{user_id}/subscription', self::class . '@updateSubscription', Auth::isAdmin());
        Router::post('/api/users/{user_id}/subscription/cancel', self::class . '@cancelSubscription', Auth::isAdmin());
        Router::post('/api/users/{user_id}/subscription/resume', self::class . '@resumeSubscription', Auth::isAdmin());

    }

    public function put($userId = null)
    {
        $response = new APIResponse();
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        error_log("Received Data: " . print_r($data, true));

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return $response->setStatusCode(400)->setData(['error' => 'Invalid JSON data'])->send();
        }


        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $birthDate = trim($data['birth_date'] ?? '');
        $address = trim($data['address'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $status = trim($data['status'] ?? null);

        if (empty($firstName) || empty($lastName) || empty($email)) {
            return $response->setStatusCode(400)->setData(['error' => 'Les champs prénom, nom et email sont obligatoires'])->send();
        }

        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'birth_date' => $birthDate,
            'address' => $address,
            'phone' => $phone
        ];

        // Only allow admins to update status
        if(Auth::isAdmin() && isset($data['status'])){
            $updateData['status'] = $status;
        }

        if (User::updateUserProfile($userId, $updateData)) {
            return $response->setStatusCode(200)->setData(['message' => 'User updated successfully'])->send();
        } else {
            return $response->setStatusCode(500)->setData(['error' => 'Failed to update user'])->send();
        }
    }

    public function updateProfile()
    {
        $response = new APIResponse();
        $userId = $_SESSION['user_id'];
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        error_log("Received Data: " . print_r($data, true));

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return $response->setStatusCode(400)->setData(['error' => 'Invalid JSON data'])->send();
        }
        
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $birthDate = trim($data['birth_date'] ?? '');
        $address = trim($data['address'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($email)) {
            return $response->setStatusCode(400)->setData(['error' => 'Les champs prénom, nom et email sont obligatoires'])->send();
        }
          $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'birth_date' => $birthDate,
            'address' => $address,
            'phone' => $phone
          ];

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleProfilePictureUpload($_FILES['profile_picture']);
            if ($uploadResult['success']) {
                $updateData['profile_picture'] = $uploadResult['filepath'];
            } else {
                return $response->setStatusCode(500)->setData(['error' => $uploadResult['message']])->send();
            }
        }
        
        if (!empty($data['current_password']) && !empty($data['new_password']) && !empty($data['confirm_password'])) {
            if ($data['new_password'] !== $data['confirm_password']) {
                return $response->setStatusCode(400)->setData(['error' => 'Les nouveaux mots de passe ne correspondent pas.'])->send();
            }
            
            $user = User::getUserById($userId);
            if(password_verify($data['current_password'], $user['password'])){
                $updateData['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
            } else {
            return $response->setStatusCode(401)->setData(['error' => 'Le mot de passe actuel est incorrect.'])->send();
            }
        }
       if (User::updateUserProfile($userId, $updateData)) {
            return $response->setStatusCode(200)->setData(['message' => 'Profil mis à jour avec succès.'])->send();
       } else {
           return $response->setStatusCode(500)->setData(['error' => 'Échec de la mise à jour du profil.'])->send();
       }
    }
        private function handleProfilePictureUpload($file)
    {
         $uploadDir = dirname(__DIR__, 2) . '/public/uploads/profile_pictures/';
             $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
             $maxFileSize = 5 * 1024 * 1024; // 5MB
         
             if (!in_array($file['type'], $allowedTypes)) {
                 return ['success' => false, 'message' => 'Type de fichier non autorisé.'];
             }
         
             if ($file['size'] > $maxFileSize) {
                 return ['success' => false, 'message' => 'Le fichier est trop volumineux.'];
             }
         
             $originalFilename = basename($file['name']);
             $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $newFilename = uniqid('profile_', true) . '.' . $extension; // Ensure a unique filename
            $targetFilepath = $uploadDir . $newFilename;
         
           if (move_uploaded_file($file['tmp_name'], $targetFilepath)) {
               return ['success' => true, 'filepath' => '/uploads/profile_pictures/' . $newFilename]; // return public url of the file.
           } else {
              return ['success' => false, 'message' => 'Erreur lors du téléchargement du fichier.'];
           }
        }


    public function showProfile()
    {
        $userId = $_SESSION['user_id'];
        $user = null;
        $response = new APIResponse();

        if (!$user) {
            $response->setStatusCode(404)->setData(['error' => 'User not found'])->send();
            return;
        }

        $response->setStatusCode(200)->setData($user)->send();
    }

      public function get($userId = null)
        {
            $response = new APIResponse();
            error_log(print_r($userId, true));
            if ($userId === null) {
                $searchTerm = $_GET['search'] ? trim($_GET['search']) : '';
                $users = !empty($searchTerm) ? User::searchUsers($searchTerm) : User::getAllUsers();
                //$users = User::searchUsers($searchTerm);
                return $response->setStatusCode(200)->setData($users)->send();
            }

            $user = User::getUserById($userId);
            if (!$user) {
                return $response->setStatusCode(404)->setData(['error' => 'User not found'])->send();
            }
            return $response->setStatusCode(200)->setData($user)->send();
        }

    public function postDelete($userId)
    {
        $response = new APIResponse();

        if (User::deleteUser($userId)) {
            return $response->setStatusCode(200)->setData(['message' => 'User deleted successfully'])->send();
        }

        return $response->setStatusCode(500)->setData(['error' => 'Failed to delete user'])->send();
    }
       public function post($userId = userId)
       {
         $response = new APIResponse();
         $data = $_POST;
         $subscriptionId = Subscription::getStripeSubscriptionId($userId);

           if (isset($data['action'])) {
                switch ($data['action']) {
                    case 'cancel':
                      return $this->cancelSubscriptionAction($userId);
                     case 'resume':
                       return $this->resumeSubscriptionAction($userId);
                   default:
                   return $response->setStatusCode(400)->setData(['error' => 'Invalid action'])->send();
               }
           }

          if (Subscription::updateSubscription($userId, $subscriptionId, $data)) {
              return $response->setStatusCode(200)->setData(['message' => 'Subscription updated successfully'])->send();
          }

           return $response->setStatusCode(500)->setData(['error' => 'Failed to update subscription'])->send();
       }

    public function getSubscriptionAction($userId)
        {
        $response = new APIResponse();
        $subscription = Subscription::getActiveSubscription($userId);

        if ($subscription) {
            return $response->setStatusCode(200)->setData($subscription)->send();
        }

        return $response->setStatusCode(404)->setData(['error' => 'Subscription not found'])->send();
    }

    private function cancelSubscriptionAction($userId)
    {
        $response = new APIResponse();
        if (Subscription::cancelSubscription($userId)) {
            $response->setStatusCode(200)->setData(['message' => 'Subscription cancelled successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to cancel subscription'])->send();
        }
    }

    public function getUserSubscription($userId)
    {
        $response = new APIResponse();

        $subscription = Subscription::getStripeSubscriptionId($userId);

        if ($subscription) {
            $response->setStatusCode(200)->setData($subscription)->send();
        } else {
            $response->setStatusCode(404)->setData(['error' => 'Subscription not found'])->send();
        }
    }

    private function resumeSubscriptionAction($userId)
    {
        $response = new APIResponse();
        if (Subscription::resumeSubscription($userId)) {
            $response->setStatusCode(200)->setData(['message' => 'Subscription resumed successfully'])->send();
        } else {
            $response->setStatusCode(500)->setData(['error' => 'Failed to resume subscription'])->send();
        }
    }
}