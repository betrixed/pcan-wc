<?php
namespace Pcan;
/**  
 * User rendering object as controller->view
 */
use WC\UserSession;
use Plates\Engine;


class HtmlPlates  extends Html    {
    
    public $engine;
    
    
    public function __construct($f3)
    {
        // setup fall back paths
        parent::__construct($f3);
        
        
        $this->engine = new Engine();
        $this->engine->setFileExtension('phtml');
        $paths = $f3->get('UI'); // path string from hive
        if (isset($paths)) {
            $backups = explode('|', $paths);
            $realpaths = [];
            foreach($backups as $id => $folder)  {
                $rp = realpath($folder);
                $realpaths[] = $rp;
                $this->engine->addFolder('path' .  $id, $rp);
            }
            $last = count($backups);
            if ($last > 0) {
                $this->engine->setDirectory($realpaths[$last-1]);
            }
        }
        $this->values['view'] = $this;
        $this->values['f3'] = $f3;
        $this->values['nav'] = 'path0::' . $f3->get('nav_lp');
        $this->engine->loadExtension(new PlatesForm());
    }

    /**
     * Add array of items to the values array.
     * Overwrites existing key => values
     * @param array $items
     */
    public function add(array $items) {
        foreach($items as $key => $val) {
            $this->values[$key] = $val;
        }
    }
    public function render() {
        $this->final_headers();
        return $this->engine->render($this->content, $this->values);
    }

}
