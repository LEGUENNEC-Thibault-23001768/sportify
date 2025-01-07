<?php

namespace Controllers;

use Core\Config;
use Exception;
use Google\Auth\OAuth2;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Models\User;

class GoogleAuthController
{
    private OAuth2 $oauth;

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

    /**
     * @return void
     */
    public function login(): void
    {
        $authUrl = $this->oauth->buildFullAuthorizationUri([
            'access_type' => 'offline',
            'prompt' => 'consent',
            'response_type' => 'code'
        ]);

        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    }

    /**
     * @return void
     */
    public function callback(): void
    {
        if (!isset($_GET['code'])) {
            header('Location: /login');
            return;
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
                // Assuming create() is a static method in User model
                // If not, instantiate User and call create on the instance
            }

            $_SESSION['user'] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email
            ];

            $_SESSION['user_id'] = $user['member_id'] ?? null;
            $_SESSION['user_email'] = $email;

            header('Location: /dashboard');
            return;
        } catch (Exception $e) {
            header('Location: /error?message=' . urlencode($e->getMessage()));
            return;
        } catch (GuzzleException $e) {
            header('Location: /error?message=' . urlencode($e->getMessage()));
            return;
        }
    }

    /**
     * @param $accessToken
     * @return mixed
     * @throws GuzzleException
     */
    private function getUserInfo($accessToken): mixed
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