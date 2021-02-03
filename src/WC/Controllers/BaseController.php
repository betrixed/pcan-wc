<?php

namespace WC\Controllers;
use Phalcon\Mvc\Controller;
/**
 * Description of BaseController
 *
 * @author michael
 */
class BaseController extends Controller {

    public function urlPrefix(): string {
        return $_SERVER['REQUEST_SCHEME']
                . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * 
     * @return string Full URL with Query String
     */
    public function getURL(): string {
        return $_SERVER['REQUEST_SCHEME'] . '://'
                . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public function https() {
        $server = $_SERVER;
        if ($server['REQUEST_SCHEME'] !== 'https') {
            $ssl_host = $this->get('ssl_host', null);
            $host = $server['HTTP_HOST'];
            // This is because a ssl certificate required a www.NAME
            if (!empty($ssl_host)) {
                $ssl_host = $ssl_host . '.';
                if (strpos($host, $ssl_host) !== 0) {
                    $host = $ssl_host . $host;
                }
            }
            $this->reroute('https://' . $host . $server['REQUEST_URI']);
            return false;
        }
        return true;
    }

    public function getPost() : array
    {
        return $_POST;
    }
    
    public function getRequest() : array
    {
        return $_REQUEST;
    }
    
    public function error($msg) {
        $this->user_session->flash($msg);
        $this->dispatcher->forward(['controller' => 'error',
                                    'action' => 'route404',
                                    'params' => [$msg]
                        ]);
    }
    public function notAuthorized() {
        $app = $this->app;
        $msg = 'No access to ' . $app->arguments;
        $this->user_session->flash($msg);
        $this->dispatcher->forward(['controller' => 'error',
                                    'action' => 'block',
                                    'params' => [$msg]
                        ]);
    }

    public function reroute($url) {
        $this->user_session->save();

        if (strpos($url, 'http') !== 0) {
            $url = $this->urlPrefix() . $url;
        }
        $response = $this->response;
        $response->redirect($url, true);
        return $response;
    }

}
