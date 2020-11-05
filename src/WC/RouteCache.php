<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC;

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Route;
use Phalcon\Cli\Router as CliRouter;
/**
 * Description of RouteCache
 *
 * @author michael
 */
use WC\RouteParse;

class RouteCache
{
    const NO_AJAX = 1;
    const ONLY_AJAX = 2;
    const ALLOW_AJAX = 3;

    static function requestIsJax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }

    static function loadRoutes(array $options): object
    {

        $routes = $options['routes'];
        $isWeb = $options['isWeb'];

        $info = pathinfo($routes);
        $cache_file = $info['dirname'] . "/." . $info['filename'] . '.ser';
        if (empty($info['extension'])) {
            $routes .= '.php';
        }
        if (file_exists($cache_file) && (filemtime($routes) < filemtime($cache_file))) {
            $archive = unserialize(file_get_contents($cache_file));
        } else {
            $archive = null;
        }

        if (!$archive) {
            $rdata = require($routes);
            $archive = RouteParse::makeRouteObjects($rdata, $isWeb);
            file_put_contents($cache_file, serialize($archive));
        }
        return self::makeRouter($archive, $isWeb);
    }

    static function makeRouter(object $route_set, bool $isWeb): object
    {
        if ($isWeb) {
            $router = new Router(false);
            $router->removeExtraSlashes(true);
        } else {
            $router = new CliRouter(false);
        }

        //$gen = $router->getIdGenerator();

        $rset = $route_set->rset;
        $notFound = $route_set->notFound;

        if (!empty($notFound)) {
            $router->notFound($notFound);
        }
        $namespace = $route_set->defaultNS;
        if ($isWeb) {
            if (!empty($namespace)) {
                $router->setDefaultNamespace($namespace);
            }
            $reqIsAjax = self::requestIsJax();
            foreach ($rset as $pos => $store) {
                $route = $store->route;
                $ajaxFlag = $store->ajaxFlag;
                $route->beforeMatch(
                        function ($uri, $route) use ($ajaxFlag, $reqIsAjax): bool {
                    switch ($ajaxFlag) {
                        case static::ALLOW_AJAX:
                            $result = true;
                            break;
                        case static::ONLY_AJAX:
                            $result = $reqIsAjax;
                            break;
                        case static::NO_AJAX:
                        default:
                            $result = !$reqIsAjax;
                    }
                    if ($result) {
                        return true;
                    }
                    return false;
                });
                $router->attach($route);
            }
        } else {
            foreach ($rset as $pos => $store) {
                $paths = $store->paths;
                $controller = "\\App\\Tasks\\" . ucfirst($paths['controller']) . "Task";

                $task = ['task' => $controller, 'action' => $paths['action'] . "Action"];
                //$handler = ucfirst($paths['controller']) . "Task" . "::" . $paths['action'];
                $router->add($store->pattern, $task);
            }
        }
        return $router;
    }

}
