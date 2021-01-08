<?php

namespace WC;


use Phalcon\Mvc\Router\Route;

/**
 * RouteParse.
 * 
 * Process route specifications in compact format lines
 * [ pattern ] => [ handler ]
 * VERB[,VERB] /([fixed]|/[:param]|/{param})* [ajax]? => <controller>-><action>
 * The :param and {param} format are treated as equivalent
 * Strategy is to eliminate possibilities as early as possible, and so present very few
 * possibilities to dynamic constructed router,
 * or build a cachable $router object with all specified, 
 * using the $doAll parameter
 * 
 *
 * @author michael
 */

use WC\RouteCache;

class RouteParse
{
    

    // make an array of pre-built Route objects for serialize, for fast Router setup.
    static function makeRouteObjects(array $input, bool $isWeb): object
    {

        $route_list = [];
        $reg1 = 'GET|POST|PUT|DELETE';
        $do1 = '/((?:' . $reg1 . ')(?:,(?:' . $reg1 . '))*)\s+/';
        $path_reg = '[_\\-\\.\\w\\d]*';
        $reg2 = ':?[\\w\\d]' . $path_reg . '|{\\w' . $path_reg . '}|\\s+\\[ajax\\??\\]$';
        $reg2 .= '|\{.*\}|\(.*\)';
               
        $obj_reg = '[a-z][_a-z0-9]*';
        $reg3 = $obj_reg . '|{' . $obj_reg . '}';

        $do2 = '/\\/?(' . $reg2 . ')/mi';
        $do3 = '/(' . $reg3 . ')->(' . $reg3 . ')/i';

        $rix = 0;
        foreach ($input as $label => $ns) {

            $rset = [];
            $rset['namespace'] = $ns['namespace'] ?? null;
            $rset['not_found'] = $ns['not_found'] ?? null;

            if (isset($ns['default']) && $ns['default']) {
                $prefix = '';
                $rset['default'] = true;
            } else {
                $prefix = $label;
            }

            $ordered = [];

            foreach ($ns['routes'] as $rt => $h) {
                $r_match = null;
                $result = preg_match($do1, $rt, $r_match);
                $pattern = '';
                $pix = 0;
                if ($result === false || count($r_match) < 2) {
                    continue;
                }

                if (strpos($r_match[1], ',') !== false) {
                    $verb = explode(',', $r_match[1]);
                } else {
                    $verb = $r_match[1];
                }


                $next_offset = strlen($r_match[0]);
                if (!empty($prefix)) {
                    $pattern = '/' . $prefix;
                }
                // see if first part of $handledUri is the same
                // repeat for action and parameters
                $remain = substr($rt, $next_offset);


                $r_match = null;
                $result = preg_match_all($do2, $remain, $r_match);
                $ajaxFlag = RouteCache::NO_AJAX;
                $params = [];
                $param_ix = 1;

                if (empty($result) || (count($r_match) < 2)) {
                    if ($remain === '/') {
                        $pattern = '/';
                        $last_seg = '/';
                    } else {
                        continue;
                    }
                }
                $segs = $r_match[1];
                $segct = count($segs);

                for ($i = 0; $i < $segct; $i++) {
                    $seg = strtolower(trim($segs[$i]));
                    $begin = substr($seg, 0, 1);
                    $end = substr($seg, -1);

                    $name = null;
                    if ($end === ']') {
                        if ($seg === '[ajax]') {
                            $ajaxFlag = RouteCache::ONLY_AJAX;
                            continue;
                        } else if ($seg === '[ajax?]') {
                            $ajaxFlag = RouteCache::ALLOW_AJAX;
                            continue;
                        }
                    }
                    if ($begin === ':') {
                        $name = substr($seg, 1);
                        $pattern .= '/{' . $name . '}';
                        $params[$name] = $param_ix;
                        $param_ix++;
                    } else if ($begin === '{') {
                        $name = substr($seg, 1, -1);
                        $pattern .= '/' . $seg;
                        $params[$name] = $param_ix;
                        $param_ix++;
                    } else {
                        $name = $seg;
                        $pattern .= '/' . $seg;
                    }
                    $last_seg = $name;
                    $pix++;
                }

                // Process Controller and Action (TODO: namespace)
                $r_match = null;
                $ctrl_ns = '';

                $result = preg_match($do3, $h, $r_match);
                if ($result === false || count($r_match) < 3) {
                    continue;
                }
                // may have to parse out namespace
                $controller = $r_match[1];

                $action = $r_match[2];
                $args = [];
                if (substr($controller, 0, 1) === '{') {
                    $needle = substr($controller, 1, -1);
                    $temp = array_search($needle, $params);
                    $args['controller'] = $temp;
                } else {
                    $args['controller'] = $controller;
                }
                if (substr($action, 0, 1) === '{') {
                    $needle = substr($action, 1, -1);
                    $temp = array_search($needle, $params);
                    $args['action'] = $temp;
                } else {
                    $args['action'] = $action;
                }
                $args = ['controller' => $controller, 'action' => $action];
                if (!empty($ctrl_ns)) {
                    $args['namespace'] = $ctrl_ns;
                }
                // [ "controller" => strtolower($controller),
                //    "action" => strtolower($action) ];

                if (count($params) > 0) {
                    foreach ($params as $name => $ix) {
                        $args[$name] = $ix;
                    }
                }
                // Make a serializable class
                $rix++;
                if ($isWeb) {
                    $route = new Route($rix, $pattern, $args, $verb);
                    $route->setName($last_seg . '-' . $rix);
                    $rs = new \stdClass();
                    $rs->route = $route;
                    $rs->ajaxFlag = $ajaxFlag;
                }
                else {
                    $rs = new \stdClass();
                    $rs->pattern = $pattern;
                    $rs->paths = $args;
                }
                $route_list[] = $rs;
            }
        }
        $result = new \stdClass();
        $result->rset = $route_list;
        $result->notFound = $rset['not_found'];
        $result->defaultNS = $rset['namespace'];

        return $result;
    }

}
