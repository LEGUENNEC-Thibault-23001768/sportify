<?php

namespace Controllers;

use Google\Auth\OAuth2;
use GuzzleHttp\Client;
use Core\View;
use core\Config;
use Models\User;

class GoogleAuthController
{
    private $view;
    private $oauth;

    public function __construct()
    {
        $this->view = new View();

        /*clientId = '857873046046355-3bigof3avgr1rgqq0ng703587g7nh4dn.apps.googleusercontent.com';
        $clientSecret = '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';
        $redirectUri = 'http://localhost:8888/callback';
        
        $clientSecrets = json_decode(file_get_contents(__DIR__ . '/../../client_secret.json'), true)['web'];
*/
        $this->oauth = new OAuth2([
            'clientId' => Config::get("google")["clientId"],
            'clientSecret' => Config::get("google")["clientSecret"],
            'authorizationUri' => Config::get("google")["authorizationUri"],
            'tokenCredentialUri' => Config::get("google")["tokenCredentialUri"],
            'redirectUri' => Config::get("google")["redirectUri"],
            'scope' => ['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile']
        ]);
    
    }

    public function login()
    {
        $authUrl = $this->oauth->buildFullAuthorizationUri([
            'access_type' => 'offline',
            'prompt' => 'consent',
            'response_type' => 'code'
        ]);

        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit();
    }

    public function callback()
    {        
        if (!isset($_GET['code'])) {
            header('Location: /login');
            exit();
        }

        try {
            
            $this->oauth->setCode($_GET['code']);
            
            $token = $this->oauth->fetchAuthToken();
            $_SESSION['google_access_token'] = $token['access_token'];

            if (isset($token['refresh_token'])) {
                $_SESSION['google_refresh_token'] = $token['refresh_token'];
            }

            $userInfo = $this->getUserInfo($token['access_token']);

            $firstName = $userInfo['given_name'];
            $lastName = $userInfo['family_name'];
            $email = $userInfo['email'];

            $user = User::findByEmail($email);

            if (!$user) {
                $userData = [
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' => null, 
                ];

                $userModel = new User();
                $userModel->create($userData);
            }

            $_SESSION['user'] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email
            ];
            $_SESSION['user_id'] = $user['member_id'];

            header('Location: /dashboard');
            exit();
        } catch (Exception $e) {
            // Gérer l'erreur, par exemple en redirigeant vers une page d'erreur
            header('Location: /error?message=' . urlencode($e->getMessage()));
            exit();
        }
    }

    private function getUserInfo($accessToken)
    {
        $client = new Client();
        $response = $client->get('https://www.googleapis.com/oauth2/v2/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}