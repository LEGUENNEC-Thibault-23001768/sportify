<?php

namespace Controllers;

use Google_Client;
use Google_Service_Oauth2;
use Core\View;
use Models\User; // Assurez-vous que le modèle User est correctement importé

class GoogleAuthController
{
    private $view;
    private $client;

    public function __construct()
    {
        $this->view = new View();

        $this->client = new Google_Client();
        $this->client->setAuthConfig(__DIR__ . '/../../client_secret.json');
        $this->client->setRedirectUri('http://localhost:8888/callback');
        $this->client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $this->client->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
        $this->client->setAccessType('offline');
    }

    public function login()
    {
        session_start();
        $authUrl = $this->client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit();
    }

    public function callback()
    {
        session_start();

        if (!isset($_GET['code'])) {
            header('Location: /login');
            exit();
        }

        $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
        $accessToken = $this->client->getAccessToken();
        $_SESSION['google_access_token'] = $accessToken;

        if (isset($accessToken['refresh_token'])) {
            $_SESSION['google_refresh_token'] = $accessToken['refresh_token'];
        }

        $oauth = new Google_Service_Oauth2($this->client);
        $userInfo = $oauth->userinfo->get();

        $firstName = $userInfo->givenName;
        $lastName = $userInfo->familyName;
        $email = $userInfo->email;

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

        header('Location: /dashboard');
        exit();
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }
}
