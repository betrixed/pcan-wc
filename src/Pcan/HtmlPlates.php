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
    
    public function __construct($f3, $path = null, $ext = null)
    {
        parent::__construct($f3, $path, $ext);

        $this->values = ['f3' => $f3];
        $this->engine = new Engine($this->path, $this->ext);
        $this->engine->loadExtension(new PlatesForm());
    }
    
    public function add(array $items) {
        foreach($items as $key => $val) {
            $this->values[$key] = $val;
        }
    }
    public function render() {
        $this->final_headers();
        return $this->engine->render($this->layout, $this->values);
    }

}
