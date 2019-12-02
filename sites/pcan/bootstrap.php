<?php
// mailchimp-api-php v3
$mailchimp =  __DIR__ . '/../../chimpv3/vendor/autoload.php';
if (file_exists($mailchimp)) {
    require $mailchimp;
}

$cfg = \WC\WConfig::fromXml($sitepath . ".secrets.xml");
$f3->set('secrets', $cfg);

if (php_sapi_name() === "cli") {
    $f3->route('GET /menuinit', function($f3) {
        echo "Init Menus" . PHP_EOL;
        $menus = new \Pcan\InitMenus();
        $menus->doAll(__DIR__);
    });
}