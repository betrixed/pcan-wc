<?php
use Phalcon\Mvc\Router;

$router = new Router(false);
    $router->setDefaultNamespace('App\Controllers');
    $router->removeExtraSlashes(true);
    
    $handledUri = $_SERVER['REQUEST_URI'];
    $handledMethod = $_SERVER['REQUEST_METHOD'];
    
    $f3routes = [
        
        'POST /admin/schema/read' => 'Schema->meta',
        'GET /admin/schema' => 'Schema->index',
        'GET /admin/schema/script/:v' => 'Schema->script',
        //'GET /' => 'Index->index',
        /*'GET /index.php' => 'Index->index',
        
        

        
        'POST /admin/schema/compare' => 'Schema->compare',
        'POST /admin/schema/initdb' => 'Schema->initdb'
         */
    ];
    $prefix = 'admin';
    $reg1 = "GET|POST";
    $reg2 .= '\\/:?\\w[\\-\\.\\w]*';
    $reg3 = '\\\\?[a-z][\\\\_a-z0-9]*';
    $reg4 = '[a-z][a-z0-9]*';
    $do1 = '/(' . $reg1 . ')\s+(' . $reg2 . '|\\/)/i';
    $do2 = '/(' . $reg2 . ')/i';
    $do3 = '/(' . $reg3 . ')->('  . $reg4 . ')/i';
    foreach($f3routes as $rt => $h) {
        $r_match = null;
        $result = preg_match($do1,$rt, $r_match);
        $pattern = '';
        if ($result === false || count($r_match) < 3) {
            continue;
        }
        // need verb and pattern. First match is whole
        $verb = $r_match[1];
        if (strtoupper($verb) !== $handledMethod) {
            continue;
        }
        $pattern .= $r_match[2];
        $next_offset = strlen($r_match[0]);

        // repeat for action and parameters
        $remain = substr($rt, $next_offset);
        $params = [];
        while(strlen($remain) > 0) {
            $r_match = null;
            $result = preg_match($do2, $remain, $r_match);
            if (($result === false) || (count($r_match) < 2)) {
                break;
            }
            $seg = $r_match[1];
            
            if (substr($seg,0,2) === '/:') {
                $pattern .= '/{' . substr($seg,2) . '}';
            }
            else {
                $pattern .= $seg;
            }
            $next_offset = strlen($r_match[0]);
            $remain = substr($remain, $next_offset);
        }
        // 
        $r_match = null;
        $result = preg_match($do3 ,$h,$r_match);
        if ($result === false || count($r_match) < 3) {
            continue;
        }
        // may have to parse out namespace
        $controller = $r_match[1];
        
        $action = $r_match[2];
        
        $args = $controller . '::' . $action;
        // [ "controller" => strtolower($controller),
                //    "action" => strtolower($action) ];
        
        $method = 'add' . ucfirst(strtolower($verb));

        $route = $router->$method($pattern, $args);

    }
return $router;
