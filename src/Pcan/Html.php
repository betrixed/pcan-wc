<?php
namespace Pcan;
/**  
 * User rendering object as controller->view
 * Fat Free built in View render
 */
use WC\UserSession;
use WC\Assets;
use WC\WConfig;

class Html {
    
    public $layout; // name of layout (outer) file
    public $content;  // name of initial (inner) content file
    
    public $nav;    // name of navigation layout file
    public $usrSess; // instance of User Session
    public $flash;  // list of flash messages
    public $url;    // used as base URL 
    
    public $f3; // Fat Free Base 
    
    public $values; // data for template as array
    public $model; // access view object properties
    
    static public $browser;
    

    public function __construct() {
        $this->layout = 'layout';
        $app = \WC\App::instance();
        
        $this->nav = $app->nav;
        $this->app = $app;
        $this->ext = 'phtml';
        $this->userSess = UserSession::read();
        $this->model = new WConfig();
        $this->values = [];
    }
    /**
     * Call just before render, to synchronize Session save
     */
    public function final_headers() {
        $this->app->render_time = microtime(true);
        // see if UserSession exists, and has flash messages
        $us = $this->userSess;
        if (!empty($us)) {
            $us->flash = $us->getMessages(); // clears messages
            $us->write(); // finalize session now
        }
    }
    /**
     * Utility function to activate registered assets
     * @param array of string, or string
     */
    public function assets($items) {
        $bundles = Assets::instance();
        $bundles->add($items);
    }

}
