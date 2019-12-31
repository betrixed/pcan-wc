<?php
return  [
        'GET /' => 'Schema->index',
        'GET /index.php' => 'Schema->index',
        'POST /home/read' => 'Schema->meta',
        'GET /schema/script/@v' => 'Schema->generate',
        'POST /schema/compare' => 'Schema->compare',
        'POST /schema/initdb' => 'Schema->initdb'
    ];