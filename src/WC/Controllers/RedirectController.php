<?php
namespace WC\Controllers;
use WC\App;

/**
 * Examine the URL and redirect to another controller / action
 *
 * @author michaelrynn@parracan.org
 */
class RedirectController extends \Phiz\Mvc\Controller
{
    public function beforeExecuteRoute($dispatcher) {
        $app = $this->app;
        $url = $app->arguments;
        $redirects = $app->redirects;
        if (!empty($redirects)) {
            if (isset($redirects[$url])) {
                $dispatcher->forward($redirects[$url]);
            }
            return false;
        }
        else {
            $dispatcher->forward(['controller' => 'error', 'action' => 'block']);
            return false;

        }
    }
    
    public function indexAction() {
        return "Unknown URL";
    }
}
