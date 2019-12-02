<?php

if (php_sapi_name() === "cli") {
    $f3->route('GET /menuinit', function($f3) {
        echo "Init Menus" . PHP_EOL;
        $menus = new \WC\InitMenus();
        $menus->doAll(__DIR__);
    });
}
