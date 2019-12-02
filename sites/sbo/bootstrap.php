<?php

    WC\Html::$browser = WC\Html::getBrowser($f3->get('AGENT'));
    $agent = &WC\Html::$browser;
    
    //$agent['name'] = 'Apple Safari';
    //$agent['version'] = '5.0.6';
    
    WC\Assets::registerAssets(
        [
            'cartjs' => ['js' => ['/js/cart.js']],
            
        ]);
    
   
    if (($agent['name'] === 'Apple Safari') && ($agent['version'] === '5.0.6'))
    {
        $f3->set('navigate', 'simple_nav.phtml');
        $bundles = WC\Assets::instance();
        $bundles->add('simple');
        // nullify bootstrap
        WC\Assets::registerAssets([
            'bootstrap' => [],
        ]);
    }
    else {
        $bundles = WC\Assets::instance();
        $bundles->add('bootstrap');
    }

if (php_sapi_name() === "cli") {
    $f3->route('GET /menuinit', function($f3) {
        echo "Init Menus" . PHP_EOL;
        $menus = new \WC\InitMenus();
        $menus->doAll(__DIR__);
    });
    $f3->route('GET /importdb', function($f3) {
        require __DIR__ . '/concrete_import.php';
    });

}

