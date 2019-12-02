<?php
namespace Pcan;
/**  
 * User rendering object as controller->view
 */
use WC\UserSession;
use League\Plates\Engine;


class HtmlPlates extends Html   {
    
    public $engine;
    public $values;
    public $layout;
    
    public function __construct($f3, $path = null, $ext = null)
    {
        if (is_null($path)) {
            $path = $f3->get('sitepath') . 'views';
        }
        if (is_null($ext)) {
            $ext = 'phtml';
        }
        $this->values = ['f3' => $f3];
        $this->engine = new Engine($path, $ext);
        $this->engine->loadExtension(new PlatesForm());
    }
    
    public function add(array $items) {
        foreach($items as $key => $val) {
            $this->values[$key] = $val;
        }
    }
    public function render() {
        // see if UserSession exists, and has flash messages
        if (UserSession::hasInstance()) {
            $us = UserSession::instance();
            $this->values['usrSess'] = $us;
            $this->values['flash'] = $us->getMessages(); // clears messages
            $us->write(); // finalize session now
        }
        return $this->engine->render($this->layout, $this->values);
    }

}
