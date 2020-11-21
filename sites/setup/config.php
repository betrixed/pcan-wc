<?php

return [
    'TZ' => 'Australia/Sydney',
    'domain' => 'hub.test',
    'organization' => 'The Organisation',
    'DEBUG' => 3,
    'theme' => '/default',
    'gallery' => '/default/gallery',
    'navigate' => 'nav.tpl',
    'cache_routes' => true,
    'sub_routes' => [
        'default' => [
            'database' => 'database',
            'routes' => 'routes',
            'schema_path' => '@site_dir/schema'
        ],
    ]
];
