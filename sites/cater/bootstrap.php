<?php

(\Models\Assets::instance())->addAssets(
        [
            'custom' => ['css' => ['/css/custom.css']]
        ]);

if (php_sapi_name() === "cli") {
    $f3->route('GET /menuinit', function($f3) {
        echo "Init Menus" . PHP_EOL;
        $menus = new \InitMenus();
        $menus->doAll(__DIR__);
    });
}