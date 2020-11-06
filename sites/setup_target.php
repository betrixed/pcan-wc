<?php

/**
 * Development mode, so show all errors
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$PKG_DIR = dirname(__DIR__);
$PHP_DIR = dirname($PKG_DIR, 3);

$target = $PHP_DIR . "/sites/setup";
$setup_dir = __DIR__ . "/setup";
$dev_target = $PHP_DIR . "/dev_target.php";

$ok = file_exists($dev_target);

if (empty($ok)) {
    require $PKG_DIR . '/src/WC/Dos.php';
    \WC\Dos::copyall($setup_dir, $target);
    copy($PKG_DIR . "/sites/site.php", $target . "/site.php");
    copy($target . "/template/dev_target.php", $dev_target);
    $ok = copy($target . "/template/index.php", $WEB_DIR . "/index.php");    
}
require $dev_target;

die;


