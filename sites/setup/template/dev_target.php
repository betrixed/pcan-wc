<?php
/**
 * Development mode, so show all errors
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$PHP_DIR = __DIR__;
$TARGET = "SETUP"; // or DEV, TEST,  SITE
$SITE_FOLDER = "setup";

if ((php_sapi_name() !== "cli")) {
    if (preg_match("@/webtools\\.php.*@", $_SERVER['REQUEST_URI'], $matches)) {
        define('PTOOLSPATH', __DIR__ . '/vendor/phalcon/devtools');
        define('BASE_PATH', __DIR__ . '/sites/webtools');
        define('APP_PATH', __DIR__ . '/sites/webtools/app');
        define('PTOOLS_IP', '192.168.');
        define('ENV_TESTING', 'testing');
        $_SERVER['SCRIPT_FILENAME'] = 'webtools.php'; // Fake it
        require_once PTOOLSPATH . '/webtools.php';
        die;
    }
}


require_once __DIR__ . "/sites/$SITE_FOLDER/site.php";


