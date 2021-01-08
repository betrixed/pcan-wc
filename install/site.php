<?php

//ini_set('opcache.enable','1');
//ini_set('opcache.preload', dirname(__DIR__,2) . '/vendor/mdryn/ews/src/preload.php');
//require_once $PHP_DIR . '/vendor/autoload.php';

Use WC\{
    App,
    Dos,
    RouteCache,
    Assets,
    HtmlGem,
    FileCache,
    UserSession,
    WConfig
};
Use WC\Db\{
    Server,
    DbQuery
};
Use Phalcon\{
    Loader,
    Security
};
use Phalcon\Cli\{
    Console,
    Dispatcher
};
use Phalcon\Di\FactoryDefault\Cli as CliDI;
Use Phalcon\Di\FactoryDefault;
Use Phalcon\Mvc\{
    View,
    Application
};
use Phalcon\{
    Logger,
    Url
};
use Phalcon\Logger\Adapter\Stream as LogStream;
use Phalcon\Logger\AdapterFactory;
use Phalcon\Logger\LoggerFactory;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Session\Manager;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use WC\Chimp\Api as ChimpApi;

function load_begin(): object
{
    global $PHP_DIR, $WEB_DIR, $TARGET, $SITE_FOLDER;

    $vendor_dir = $PHP_DIR . "/vendor";
    $pcan_dir = $vendor_dir . "/betrixed/pcan";

    $loader = new Loader();

    // namespaces and folders are case sensitive
    // TODO: allow composer autoload to some of this

    $mullie = $vendor_dir . "/matthiasmullie";
    $loader->registerNamespaces(
            [
                'App' => $pcan_dir . "/src/App/",
                'WC' => $pcan_dir . "/src/WC/",
                "MatthiasMullie\\Minify" => $mullie . "/minify/src/",
                "MatthiasMullie\\PathConverter" => $mullie . "/path-converter/src/",
                "PHPMailer\PHPMailer" => $vendor_dir . "/phpmailer/phpmailer/src/",
                "Masterminds" => $vendor_dir . "/masterminds/html5/src/",
                'Soundasleep' => $pcan_dir . "/src/Soundasleep/",
            ]
    );
    $loader->register();
    $app = new App();
    // things to be established as soon as possible.
    $app->vendor_dir = $vendor_dir;
    $app->pcan_dir = $pcan_dir;
    $app->isWeb = (php_sapi_name() !== "cli");
    $app->php_dir = $PHP_DIR;
    $app->web_dir = $WEB_DIR;
    $app->target = $TARGET; // like TEST, SITE , DEV

    if (empty($SITE_FOLDER)) {
        $SITE_FOLDER = "setup";
    }
    $app->site_folder = $SITE_FOLDER;

    $sites_path = $PHP_DIR . "/sites";
    if (!file_exists($sites_path) || !is_dir($sites_path)) {
        throw \Exception("Directories Configuation Error: missing " . $sites_path);
    }

    $site_dir = $sites_path . "/" . $SITE_FOLDER;

    if (!file_exists($site_dir) || !is_dir($site_dir)) {
        $sites_path = $pcan_dir . "/sites";
        $site_dir = $sites_path . "/" . $SITE_FOLDER;
    }
    if (!file_exists($site_dir) || !is_dir($site_dir)) {
        throw new \Exception("Directories Configuation Error: missing " . $site_dir);
    }
    $app->site_dir = $site_dir;

    $config_file = $site_dir . '/config.php';
    if (file_exists($config_file)) {
        $data = WConfig::serialCache($config_file);
        $app->addArray($data);
    } else {
        throw new \Exception("Expect site configuration file " . $config_file);
    }

    if ($app->has("TZ")) {
        date_default_timezone_set($app->TZ);
    }

    $isModule = false;
    $routes_suffix = 'routes.php';
    if ($app->isWeb) {
        $uri = $_SERVER["REQUEST_URI"];
        // important property
        $app->arguments = $uri;
        if ($app->has("sub_routes")) {
            // pre-identify known module names from first argument
            if (strlen($uri) > 1) {
                $got = explode("/", substr($uri, 1));
                if (count($got) > 0) {
                    $isModule = setup_module($app, $got[0]);
                }
            }
        }
    } else {
        $app->arguments = get_cli_arguments();
    }
    if (!$isModule) {
        setup_module($app, 'default');
    }


    if (!isset($app->theme)) {
        $app->theme = 'default';
    }
    $theme_dir = $app->web_dir . "/" . $app->theme;
    if (!file_exists($theme_dir)) {

        Dos::copyAll($pcan_dir . "/web/default", $theme_dir);
    }

    // mobile detect is one class file
    require_once $vendor_dir . "/mobiledetect/mobiledetectlib/Mobile_Detect.php";
    $device_detect = new \Mobile_Detect(); // not entirely reliable
    $app->device_detect = $device_detect;
    $app->isMobile = $device_detect->isMobile();
    return $app;
}

function setup_module(object $app, string $name): bool
{
    $cfg = $app->sub_routes[$name] ?? null;
    if (!is_null($cfg)) {
        $app->module_name = $name;
        $app->module_cfg = $cfg;
        $app->routes = $cfg['routes'] ?? 'routes';
        return true;
    }
    return false;
}

function setup_world(object $app): object
{
    $ds = DIRECTORY_SEPARATOR;
    $temp = $app->site_dir . '/tmp';
    $app->temp_dir = $temp;
    if (!file_exists($temp)) {
        mkdir($temp);
    }
    $cache_dir = $temp . '/cache';
    $logs_dir = $temp . '/logs';
    $app->logs_dir = $logs_dir;
    if (!file_exists($logs_dir)) {
        mkdir($logs_dir);
    }

    $app->cache_dir = $cache_dir;
    $app->schema_dir = $app->site_dir . '/schema';
    $app->model_cache = [
        'defaultSerializer' => 'php',
        'lifetime' => 7200,
        'storageDir' => $cache_dir
    ];



    $ui_path = [$app->site_dir . '/views/'];

    $src_root = $app->pcan_dir;
    $app->web_files = $src_root . '/web';
    $ui_path[] = $src_root . '/views/app/';


    // layout of subdirectories of views in $ui_path
    $app->plates = new WConfig([
        'ext' => 'phtml',
        'UI' => $ui_path,
        'layoutsDir' => 'controllers/',
        'partialsDir' => 'partials/',
        'layout_view' => 'layout_plates',
        'outer_view' => 'main',
        'nav_view' => 'nav'
    ]);
    $container = ($app->isWeb) ? new FactoryDefault() : new CliDI();

    $container->setShared('app', $app);
    $app->assets = new Assets($app);
    $container->setShared('assets', $app->assets);
    return $container;
}

// Get arguments for CLI called from global
function get_cli_arguments(): array
{
    global $argv;
    $arguments = [];
    $params = [];

    foreach ($argv as $k => $arg) {
        if ($k === 1) {
            $arguments['task'] = $arg;
        } elseif ($k === 2) {
            $arguments['action'] = $arg;
        } elseif ($k >= 3) {
            $params[] = $arg;
        }
    }
    $arguments[] = $params;
    return $arguments;

    //return implode("/",array_slice($argv,1));
}

function setup_services($container, $app): object
{
    $container->setShared('php_engine', function() {
        return new PhpEngine();
    });
    $app->services = $container;
    $app->user_session = new UserSession($app);
    $container->setShared('user_session', $app->user_session);
    switch ($app->target) {
        case "DEV":
            $app->loadSecrets($app->site_dir . "/.dev_secrets.xml");
            break;
        case "SITE":
            $app->loadSecrets($app->site_dir . "/.site_secrets.xml");
            break;
        case "SETUP":
            $app->user_session->setAdmin();
            $app->loadSecrets($app->site_dir . "/.setup_secrets.xml");
            break;
    }

    if ($app->isWeb) {

        $container->set('file_cache', function() use ($app) {
            $cache = new FileCache($app->model_cache);
            return $cache;
        });
        $container->set(
                'view',
                function () use ($app) {
            $view = new View();
            $plates = $app->plates;
            $app->render_time = microtime(true);
            $view->setViewsDir($plates->UI);
            $view->setLayoutsDir($plates->layoutsDir);
            $view->setPartialsDir($plates->partialsDir);
            $view->setMainView('controllers/main');
            $vars = ['app' => $app,
                'm' => new WConfig(),
                'assets' => $app->assets,
                'view' => $view
            ];

            $view->setVars($vars);
            $view->registerEngines([
                '.phtml' => new PhpEngine($view, $app->services)
            ]);
            return $view;
        }
        );

        $container->setShared('logger', function() {
            global $app;
            $adapters = [
                "main" => new LogStream($app->logs_dir . "/main.log")
            ];
            $adapterFactory = new AdapterFactory();
            $loggerFactory = new LoggerFactory($adapterFactory);
            $logger = $loggerFactory->newInstance('main', $adapters);
            return $logger;
        });


        $container->setShared('session', function() use($app) {
            $tmp = $app->temp_dir;
            $sess = new Manager();
            $sess->setAdapter(new Stream(['savePath' => $tmp]));
            $sess->start();
            return $sess;
        });
        $container->setShared('security', function() {
            $security = new Security();
            $security->setWorkFactor(12);
            return $security;
        });
    } else {
        //CLI
        $dispatcher = new \Phalcon\Cli\Dispatcher();
        $dispatcher->setDefaultNamespace("WC\\Tasks");
        $container->setShared('dispatcher', $dispatcher);
    }

    $container->setShared('htmlgem', function() {
        return new HtmlGem();
    });
    $dbconfig = $app->getSecrets('db-config');

    if (!empty($dbconfig)) {
        $dbconfig_name = $app->module_cfg['database'];
        $server = new Server($dbconfig_name, $dbconfig);

        $container->setShared('server', $server);
        $container->setShared('db', function() use($server) {
            return $server->db();
        });
        $container->setShared('dbq', function() use($app) {
            $db = $app->services->getShared('db');
            return new DbQuery($db);
        });
    }

    $chimp_config = $app->getSecrets('chimp');
    if (!empty($chimp_config)) {
        $container->setShared('chimp_api', function() use($app) {
            $s = $app->getSecrets('chimp');
            return new ChimpApi($app, $s);
        });
    }

    $container->set(
            'url',
            function () {
        $url = new Url();

        $url->setBaseUri('/');

        return $url;
    });



    if ($app->isWeb) {
        $application = new Application($container);
        /* All action handlers must return a 
          Response class instance OR a string with full HTML content,
          because implicitView is false
         */
        $application->useImplicitView(false);
    } else {
        $application = new Console($container);
    }

    $app->route_time = microtime(true);
    $router = setup_router($app);
    $app->route_time = (microtime(true) - $app->route_time) * 1000.0;
    $container->set('router', $router);

    return $application;
}

function setup_router($app): object
{
    $doCache = true;

    $web = $app->isWeb;
    $options = [];
    $rname = '';
    $rdir = $app->site_dir . '/routes';
    if ($web) {
        $uri = $app->arguments;


        $options['isWeb'] = true;
        $options['routes'] = $rdir . '/' . $app->routes;
    } else {
        $options['isWeb'] = false;
        $options['routes'] = $rdir . '/cli_routes.php';
    }
    $router = RouteCache::loadRoutes($options);
    return $router;
}

/* First we need a loader for the necessary */





try {


    $app = load_begin();
    if (PHP_VERSION_ID < 80000) {
        require_once $app->pcan_dir . "/src/php80.php";
    }
    $container = setup_world($app);
    $phalcon = setup_services($container, $app);
    $response = $phalcon->handle($app->arguments);
    if ($app->isWeb && $response) {
        $response->send();
    }
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}