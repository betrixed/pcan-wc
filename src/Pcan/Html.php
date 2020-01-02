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
    

    public function __construct($f3 ) {
        $this->layout = 'layout';
        $this->nav = $f3->get('nav');
        $this->f3 = $f3;
        $this->ext = 'phtml';
        
        $this->model = new WConfig();
        $this->values = [];

        //$f3->set('UI', $path . '/|' . $f3->get('pkg') . 'views/');
        
        //$agent = $f3->get('AGENT');
       
    }
    /**
     * Call just before render, to synchronize Session save
     */
    public function final_headers() {
        $this->f3->set('render_time', microtime(true));
        // see if UserSession exists, and has flash messages
        if (UserSession::hasInstance()) {
            $us = UserSession::instance();
            $this->usrSess = $us;
            $this->flash = $us->getMessages(); // clears messages
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
