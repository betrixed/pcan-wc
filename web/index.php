<?php
require_once '../private/vendor/autoload.php';
$app = \WC\App::init_app(__DIR__, dirname(__DIR__) . '/private', 'default');
$app->run();