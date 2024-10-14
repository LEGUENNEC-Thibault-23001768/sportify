<?php

    session_start();

    $client = new Google_Client();
    $client->setClientId('857873046355-3bigof3avgr1rgqq0ng703587g7nh4dn.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('http://localhost:8888/callback');
    $client->addScope("email");
    $client->addScope("profile");

    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);

        $oauth2 = new Google_Service_Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        $_SESSION['email'] = $userInfo->email;
        $_SESSION['name'] = $userInfo->name;
        $_SESSION['picture'] = $userInfo->picture;

        header('Location: dashboard.php');
        exit();
    } else {
        header('Location: login.php');
        exit();
    }


?>