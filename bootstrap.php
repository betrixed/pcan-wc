<?php

// Initialize CMS
function init_app($webpath, $folder) {
    require_once __DIR__ . '/vendor/autoload.php';
    $f3 = \Base::Instance();
    
    $php = __DIR__ . '/';
    $sitepath = $php . "sites/" . $folder . '/';
    $f3->set('web', $webpath . '/');
    $f3->set('php', $php);
    $f3->set('sitepath', $sitepath);
    $f3->set('AUTOLOAD', $php . 'src/|' . $sitepath . 'src/');

    \WC\App::Instance()->init($f3, $sitepath);
    return $f3;
}
