<?php


define('IS_LOCAL', true);


define("SERVER_URL",  IS_LOCAL ? 'http://localhost:8080' : 'https://sportify.alwaysdata.net',);

return [
    'brand' => 'Sportify',
    'view_path' => __DIR__ . '/src/Views/', 
    'redirect_login' => '/login',
    'redirect_dashboard' => '/dashboard',
    'redirect_register' => '/register',
    'stripe_key' => '',
    'gemini_key' => '',
    'server_url' => SERVER_URL,
    'db_host' => '',
    'db_user' => '',
    'db_pass' => '',
    'db_name' => '',
    'mail_parts' => [
        'mail_head' => "<html>\r\n<head>\r\n\r\n<meta charset='utf-8'>\r\n\r\n<title>",
        'mail_title' => '[TITLE]',
        'mail_head_end' => "</title>\r\n</head>\r\n<body>",
        'mail_body' => "
            <h2>Bienvenue sur Sportify !</h2>
            <p>[PARAGRAPH]</p>
            <p><a href='[VERIFY_URL]'>[ANCHOR]</a></p>
            <img src='https://i.postimg.cc/wTWZmp2r/Sport-400-x-250-px-300-x-100-px-2.png' alt='Logo Sportify'>
            ",
        'mail_footer' => "</body>\r\n</html>"
    ],

    "google" => [
        'clientId' => "",
        'clientSecret' => "",
        'authorizationUri' => "",
        'tokenCredentialUri' => "",
        'redirectUri' => SERVER_URL . "/callback",
    ]
];

