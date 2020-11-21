<?php

/**
 * Development mode, so show all errors
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



function copy_setup() : bool {
    global $WEB_DIR;
    $PKG_DIR = dirname(__DIR__);
    $PHP_DIR = dirname($PKG_DIR, 3);


    
    $target = $PHP_DIR . "/sites/setup";
    $setup_dir = __DIR__ . "/setup";
    $dev_target = $PHP_DIR . "/dev_target.php";

    $ok = file_exists($dev_target);

    if (empty($ok)) {
        require $PKG_DIR . '/src/WC/Dos.php';
        \WC\Dos::copyall($setup_dir, $target);
        $ok = copy($PKG_DIR . "/install/site.php", $target . "/site.php");
        if ($ok) {
            $ok = copy($PKG_DIR . "/install/dev_target.php", $dev_target);
        }
        if ($ok) {
            $ok = copy($PKG_DIR . "/install/index.php", $WEB_DIR . "/index.php");    
        }
    }
    if ($ok) {
        require $dev_target;
    }
    if (!$ok) {
        echo "First setup failed" . PHP_EOL;
    }
    return $ok;
}

copy_setup();



