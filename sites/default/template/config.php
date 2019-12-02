 <?php

return [
    "globals" => [
        "TEMP" => '${sitepath}tmp/',
        'TZ' => 'Australia/Sydney',
        'domain' => '$$_DOMAIN_$$',
        'DEBUG' => 3,
        'theme' => '/$$_SITE_$$',
        'gallery' => '/$$_SITE_$$/gallery',
    ],
    "routes" => [
        'GET /' => 'Home->index',
        'GET /index.php' => 'Home->index',
    ]
];
