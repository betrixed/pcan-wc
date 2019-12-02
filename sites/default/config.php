<?php

return [
    "globals" => [
        "TEMP" => '${sitepath}tmp/',
        'TZ' => 'Australia/Sydney',
        'domain' => 'pcan.test',
        'DEBUG' => 3,
        'theme' => '/default',
        'gallery' => '/default/gallery',
    ],
    "routes" => [
        'GET /' => 'Schema->index',
        'GET /index.php' => 'Schema->index',
        'POST /home/read' => 'Schema->meta',
        'GET /schema/script/@v' => 'Schema->generate',
        'POST /schema/compare' => 'Schema->compare',
        'POST /schema/initdb' => 'Schema->initdb'
    ]
];
