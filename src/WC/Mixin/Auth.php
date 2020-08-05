<?php

namespace WC\Mixin;
use Phalcon\Mvc\Dispatcher;
/**
 * Add to controller if login required
 *
 * @author michael rynn
 */
use \WC\UserSession;
use \WC\App;

trait Auth {
    
    /**
     * 
     * @return boolean
     */
    public function beforeExecuteRoute($dispatcher) {
        if (!$this->user_session->auth($this->getAllowRole())) {
            $dispatcher->forward(['controller' => 'error', 'action' => 'block']);
            return false;
        }
        return true;
    }
    

    /**
     * URL protocol and host
     * @return string
     */
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

    public function notAuthorized() {
        $app = $this->app;
        $this->user_session->flash('No access to ' . $app->arguments);
        $this->reroute('/error/block');
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
