<?php
namespace WC;
/**  
 * User rendering object as controller->view
 */
use WC\UserSession;
use Plates\Engine;


class HtmlPlates  extends Html    {
    
    public $engine;
    public $userSession;
    
    public function __construct()
    {
        // setup fall back paths
        parent::__construct();
        $this->engine = new Engine();
        $plates = $this->app->plates;
        $this->engine->setFileExtension($plates->ext);
        $backups = $plates->UI;
        if (isset($backups)) {
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
        // view defaults
        $us = $this->userSess;
        if (!empty($us) && $us->hasRole('Admin')) {
            $this->layout = 'layout_admin';
            $this->nav = 'nav_admin';
        }
        else {
            $this->layout = $plates->layout_view;
            $this->nav = $plates->nav_view;
        }
        // values which will be auto-extracted into the view
        $this->values['m'] = $this->model;
        $this->values['view'] = $this;
        $this->values['app'] = $this->app;
        $this->values['sess'] = $us;
        $this->values['assets'] = Assets::instance();
    }

    /** any object, there are two references to it */
    public function setModel($model) {
        $this->model;
        $this->values['m'] =$model;
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
    
    /** 
     * Generate text from templates.
     * Calls render($writeHeaders = false)
     */
    public function renderView() {
        return $this->render(false);
    }
    /**
     * Render the content view, with data values.
     * Pass false, if no headers are to be written before render
     * @param bool $writeHeaders default true
     * @return type
     */
    public function render($tpl = null, $writeHeaders = true) {
        if (!empty($tpl)) {
            $this->content = $tpl;
        }
        if ($writeHeaders) {
            $this->final_headers();
        }
        return $this->engine->render($this->content, $this->values);
    }


}
