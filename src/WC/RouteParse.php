<?php

namespace WC;

use Phalcon\Mvc\Router;
use WC\App;

/**
 * Description of RouteParse
 *
 * @author michael
 */
class RouteParse
{

    static function makeRouter(array $input, bool $doAll = true) : ?Router
    {

        $router = new Router(false);
        
        $router->removeExtraSlashes(true);
        /*$router->notFound([
            'namespace' => 'Mod\Controllers',
            'controller' => 'errors',
            'action' => 'show404',
        ]);*/
        
        $handledUri = $_SERVER['REQUEST_URI'];
        $handledMethod = $_SERVER['REQUEST_METHOD'];
        $querystr = $_SERVER['QUERY_STRING'];
        if (!empty($querystr)) {
            $handledUri = substr($handledUri, 0, -1-strlen($querystr));
        }
        App::instance()->handledUri = $handledUri;


        //$prefix = 'admin';
        $reg1 = 'GET|POST|PUT|DELETE';
        $do1 = '/((?:' . $reg1 . ')(?:,(?:' . $reg1 . '))*)\s+/';
        $path_reg = '[_\\-\\.\\w\\d]*';
        $reg2 = ':?[\\w\\d]' . $path_reg . '|{\\w' . $path_reg . '}|\\s+\\[ajax\\]$';

        $obj_reg = '[a-z][_a-z0-9]*';
        $reg3 = $obj_reg . '|{' . $obj_reg . '}';
       
        $do2 = '/\\/?(' . $reg2 . ')/mi';

        $do3 = '/(' . $reg3 . ')->(' . $reg3 . ')/i';
        $uchew = $handledUri;
 
        $uparts = explode('/', $handledUri);
        $uct = count($uparts);
        if ($uct > 0) {
            $last = $uparts[$uct-1];
            $ix = strpos($last, '?');
            if ($ix !== false) {
                $uparts[$uct-1] = substr($last,0,$ix);
            }
            $uparts = array_slice($uparts,1);
            $uct = count($uparts);

        }

        foreach ($input as $prefix => $f3routes) {
            
            $namespace = $f3routes['namespace'];
            $router->setDefaultNamespace($namespace);
            if (isset($f3routes['default']) && $f3routes['default']) {
                $prefix = '';
            }
            foreach ($f3routes['routes'] as $rt => $h) {
                $r_match = null;
                $result = preg_match($do1, $rt, $r_match);
                $pattern = '';
                $pix = 0;
                if ($result === false || count($r_match) < 2) {
                    continue;
                }
                if (!doAll) {
                    if (strpos($r_match[1],$handledMethod) !== false) {
                        $verb = $handledMethod;
                    }
                    else {
                        continue;
                    }
                }
                else {
                    if (strpos($r_match[1],',') !== false) {
                        $verb = explode(',',$r_match[1]);
                    }
                    else {
                        $verb = $r_match[1];
                    }
                }
                $next_offset = strlen($r_match[0]);
                if (!empty($prefix)) {
                    $pattern = '/' . $prefix;
                }
                // see if first part of $handledUri is the same
                
                
                // repeat for action and parameters
                $remain = substr($rt, $next_offset);
                $params = [];
                $param_ix = 1;
                
                $r_match = null;
                $result = preg_match_all($do2, $remain, $r_match);

                if (empty($result ) || (count($r_match) < 2)) {
                    break;
                }
                $segs = $r_match[1];
                $segct = count($segs);
                $isAjax = false;
                for($i = 0; $i < $segct; $i++) {
                    $seg = $segs[$i];
                    $begin = substr($seg, 0, 1);
                    $end = substr($seg,-1);
                    
                    $name = null;
                    
                    if ($end === ']') {
                        if (strtolower(trim($seg)) === '[ajax]') {
                            $isAjax = true;
                            continue;
                        }
                    }
                    if ($begin === ':') {
                        $name = substr($seg, 1);
                        $pattern .= '/{' . $name . '}';
                        $params[$name] = $param_ix;
                        $param_ix++;
                    }
                    else if ($begin === '{') {
                        $name = substr($seg, 1, -1);
                        $pattern .= '/' . $seg;
                        $params[$name] = $param_ix;
                        $param_ix++;
                    } else {
                        $name = $seg;
                        $pattern .= '/' . $seg;
                    }
                    
                    
                    $pix++;
                    if (empty($name)) {
                        // not a parameter, so expect match to URI
                        if (!$doAll && ($pix >= $uct)){
                            break;
                        }
                    }
                    
                }
                // 
                $r_match = null;
                $result = preg_match($do3, $h, $r_match);
                if ($result === false || count($r_match) < 3) {
                    continue;
                }
                // may have to parse out namespace
                $controller = $r_match[1];

                $action = $r_match[2];
                $args = [];
                if (substr($controller,0,1) === '{') {
                    $needle = substr($controller,1,-1);
                    $temp = array_search($needle, $params);
                    $args['controller'] = $temp;
                }
                else {
                    $args['controller'] = $controller;
                }
                if (substr($action,0,1) === '{') {
                    $needle = substr($action,1,-1);
                    $temp = array_search($needle, $params);
                    $args['action'] = $temp;
                }
                else {
                    $args['action'] = $action;
                }
                $args = ['controller' => $controller, 'action' => $action];
                if (!empty($namespace)) {
                    $args['namespace'] = $namespace;
                }
                // [ "controller" => strtolower($controller),
                //    "action" => strtolower($action) ];
                
                if (count($params) > 0) {
                    foreach($params as $name => $ix) {
                        $args[$name] = $ix;
                    }
                }
                if (is_string($verb)) {
                    $method = 'add' . ucfirst(strtolower($verb));
                    $route = $router->$method($pattern, $args);
                }
                else if (is_array($verb)) {
                    $route = $router->add($pattern,$args);
                    $route->via($verb);
                }
                
                $route->beforeMatch(
                        function ($uri, $route) use ($isAjax) {
                            // Check if the request was made with Ajax
                            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
                                    && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                                return $isAjax;
                            }
                            return !$isAjax;    
                        }
                );

            }
        }
        return $router;
    }

}
