<?php
namespace Pcan;
/**  
 * User rendering object as controller->view
 */
use WC\UserSession;
use Plates\Engine;


class HtmlPlates extends Html   {
    
    public $engine;
    public $values;
    
    public function __construct($f3, $path = null, $ext = null)
    {
        parent::__construct($f3, $path, $ext);

        // setup fall back paths
        
        
        $this->engine = new Engine();
        $this->engine->setFileExtension('phtml');
        $paths = $f3->get('UI');
        if (isset($paths)) {
            $backups = explode('|', $paths);
            $realpaths = [];
            foreach($backups as $id => $folder)  {
                $realpaths[] = realpath($folder);
                $this->engine->addFolder('path' .  $id, $realpaths[$id]);
            }
            // Only seems to support 1 backup so far
            if (count($backups) > 0) {
                $this->engine->setDirectory($realpaths[0]);
            }
        }
        $this->values['view'] = $this;
        $this->values['f3'] = $f3;
        $this->values['nav'] = 'path0::' . $f3->get('nav_lp');
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
