<?php


define('IS_LOCAL', true);

ini_set('SMTP', 'smtp-sportify.alwaysdata.net');
ini_set('smtp_port', 587);
ini_set('sendmail_from', 'sportify@alwaysdata.net');

define("SERVER_URL",  IS_LOCAL ? 'http://localhost:8080' : 'https://sportify.alwaysdata.net',);

return [
    'brand' => 'Sportify',
    'view_path' => __DIR__ . '/src/Views/', // TODO add other configs and modify.
    'redirect_login' => '/login',
    'redirect_dashboard' => '/dashboard',
    'redirect_register' => '/register',
    'stripe_key' => 'sk_test_51Q80Nv01Olm6yDgOjM3A9yXbw0WgaWxqmrh4Xfjnfh2kwTmFlAyzplOz5jIfnzUm9y3iGrCZqrsgfBwn81ofPb9X00hLSncyxX',
    'gemini_key' => 'AIzaSyA1ddcTD8aE14BeSwgd0ceJUQtyH93YCH4',
    'server_url' => SERVER_URL,
    'db_host' => 'mysql-sportify.alwaysdata.net',
    'db_user' => 'sportify',
    'db_pass' => 'lechatrouge',
    'db_name' => 'sportify_db',
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
        'clientId' => "857873046355-3bigof3avgr1rgqq0ng703587g7nh4dn.apps.googleusercontent.com",
        'clientSecret' => "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",
        'authorizationUri' => "https://accounts.google.com/o/oauth2/auth",
        'tokenCredentialUri' => "https://oauth2.googleapis.com/token",
        'redirectUri' => SERVER_URL . "/callback",
    ]
];

