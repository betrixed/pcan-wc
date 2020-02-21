<?php
require_once '../private/vendor/autoload.php';
\WC\App::run_app(__DIR__, dirname(__DIR__) . '/private', '$$_SITE_$$');