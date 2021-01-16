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
use Phalcon\Mvc\RouterInterface;
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

    static function loadRoutes(RouterInterface $router, array $options): object
    {
        global $DEBUG_TRACE;
        $routes = $options['routes'];
        $isWeb = $options['isWeb'];
        $doCache = $options['cache'] ?? true;
        $info = pathinfo($routes);
        $cache_file = $info['dirname'] . "/." . $info['filename'] . '.ser';
        if (empty($info['extension'])) {
            $routes .= '.php';
        }
        if ($doCache && file_exists($cache_file) && (filemtime($routes) < filemtime($cache_file))) {
            $archive = unserialize(file_get_contents($cache_file));
        } else {
            $archive = null;
        }

        if (!$archive) {
            
            $rdata = require($routes);
             if ( $DEBUG_TRACE) {
                debugLine("Routes file " . $routes);
             }
            $archive = RouteParse::makeRouteObjects($rdata, $isWeb);
            if ($doCache) {
                file_put_contents($cache_file, serialize($archive));
            }
        }
        return self::makeRouter($router, $archive, $isWeb);
    }

    static function makeRouter(RouterInterface $router, object $route_set, bool $isWeb): object
    {
        global $DEBUG_TRACE;
        //$gen = $router->getIdGenerator();

        $rset = $route_set->rset;
        $notFound = $route_set->notFound;

        if (!empty($notFound)) {
            $router->notFound($notFound);
        }
        $namespace = $route_set->defaultNS;
        if ( $DEBUG_TRACE) {
            debugLine("Default Namespace " . $namespace);
        }
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
                    debugLine("AJAX error flag : $ajaxFlag , $reqIsAjax");
                    return false;
                });
                $router->attach($route);
            }
        } else {
            foreach ($rset as $pos => $store) {
                $paths = $store->paths;
                $controller = "\\WC\\Tasks\\" . ucfirst($paths['controller']) . "Task";

                $task = ['task' => $controller, 'action' => $paths['action'] . "Action"];
                //$handler = ucfirst($paths['controller']) . "Task" . "::" . $paths['action'];
                $router->add($store->pattern, $task);
            }
        }
        return $router;
    }

}
