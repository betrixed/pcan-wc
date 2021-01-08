<?php


/**
 * Controller names with '_' are CamelCased with "Controller" appended.
 * ->Action names are not CamelCased and just have "Action appended.
 */
return [
    "app" => [
        "namespace" => "WC\Controllers",
        "default" => true,
        "not_found" => ["controller" => 'error', "action" => 'route404'],
        "routes" => [
        'GET /' => 'schema->index',
        'GET /index.php' => 'schema->index',
        'GET /admin/schema' => 'schema->index',
        'POST /admin/schema/read' => 'schema->meta',
        'GET /admin/schema/script/:v' => 'schema->script',
        'POST /admin/schema/compare' => 'schema->compare',
        'POST /admin/schema/initdb' => 'schema->initdb'
        ]
    ]
];