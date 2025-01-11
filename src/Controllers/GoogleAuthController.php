<?php

namespace Controllers;

use Google\Auth\OAuth2;
use GuzzleHttp\Client;
use Core\Config;
use Models\User;
use Core\Router;
use Core\RouteProvider;


class GoogleAuthController implements RouteProvider
{
    public static function routes(): void
    {
        Router::get('/google', self::class . '@login');
        Router::get('/callback', self::class . '@callback');
    }
    
    private $oauth;

    public function __construct()
    {
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

                User::create($userData);
            }

            $_SESSION['user'] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email
            ];
            
            $_SESSION['user_id'] = isset($user['member_id']) ? $user['member_id'] : null;
            $_SESSION['user_email'] = $email;

            header('Location: /dashboard');
            exit();
        } catch (\Exception $e) {
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