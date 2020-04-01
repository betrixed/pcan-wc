<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\View;
use Phalcon\Url;
use Phalcon\Loader;
use Phalcon\Collection;
use Phalcon\Mvc\Router;

use WC\WConfig;
use WC\App;
use WC\Assets;


function setup_base($container)
{
    $app_path = __DIR__;

    $loader = new Loader();
    // namespaces and folders are case sensitive
    $loader->registerNamespaces(
           [
               'App' => __DIR__ . '/src/',
           ]
    );
    

    $loader->register();

    
    $app = \WC\App::instance();
    $app->WEB = WEB_DIR;
    $app->PHP = PHP_DIR;
    $app->APP = __DIR__;
    $app->TEMP = __DIR__ . '/tmp';
    $app->CACHE = __DIR__ . '/tmp/cache';
    $app->SCHEMA = __DIR__ . '/schema';
    $app->theme = '/pcan';
    
    $ui_path = [__DIR__ . '/views' ];
    
    if (!file_exists(PHP_DIR . '/vendor')) {
        $ui_path[] = PHP_DIR . '/views/pcan';
    }
    else {
        $app_class_dir = \WC\App::mypath();
        $ui_path[] = dirname($app_class_dir,2) . '/views/pcan';
    }
    $app->plates = new WConfig([
        'ext' => 'phtml',
        'UI' => $ui_path,
        'layoutsDir' => 'controllers',
        'layout_view' => 'layout_plates',
        'outer_view' => 'main',
        'nav_view' => 'nav'
    ]);
    
    $container->set('app', $app);

    $container->set(
            'view',
            function () {
                $view = new View();
                $app = App::instance();
                $app->render_time = microtime(true);
                $view->setViewsDir($app->plates->UI);
                $view->setLayoutsDir('controllers/');
                $view->setTemplateAfter('main');
                $vars = ['app' => $app, 'm' => new WConfig(), 'assets' => Assets::instance()];       
                $view->setVars($vars);
                $view->registerEngines([
                        '.phtml'   => 'WC\ViewEngine'
                        ]);
                return $view;
            }
    );

    
    $router = require __DIR__ . '/mvc_router.php';
    
    $container->set ( 'router', $router );
    
    $container->set(
            'url',
            function () {
        $url = new Url();

        $url->setBaseUri('/');

        return $url;
    }
    );
}

$container = new FactoryDefault();

setup_base($container);

$application = new Application($container);
$application->useImplicitView(false);
try {
    $response = $application->handle(
            $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}