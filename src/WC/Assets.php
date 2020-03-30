<?php

namespace WC;

use MatthiasMullie\Minify;

/**
 * Organise typical webpage .css, .js assets
 */
class Assets  {
    static public $instance;
    
    static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new Assets();
        }
        return self::$instance;
    }
    private $mark;
    private $order;
    private $loggedIn;
    private $assetSrc;
    private $assetProd;
    private $blobs;
    private $web;
    private $f3;
    private $config;
    private $minify;
    private $minify_name;
    // Minify cache
    const MAX_AGE = 60*60*24; 
    
    static public function registerAssets($assets) {
        $si = static::instance();
        $si->addAssets($assets);
    }

    public function minify($name) {
        $this->minify_name = $name;
        $this->minify = true;
    }

    public function addAssets(array $cfg) {
        if (is_array($this->config)) {
            $this->config = array_merge($this->config, $cfg);
        } else { // is_object
            foreach ($cfg as $key => $value) {
                $this->config->$key = $value;
            }
        }
    }

    public function __construct() {
        $this->app = App::Instance();
        $app = $this->app;
        $path = $app->APP . "/assets.xml";
        if (!file_exists($path)) {
            $path = $app->PHP . "/config/assets.xml";
        }
        $this->web = $app->WEB;

        $cfg = WConfig::fromXml($path);
        $this->config = $cfg;
        $this->assetSrc = $cfg['assetSrc'];
        $this->assetProd = $cfg['assetCache'];
        $this->order = [];
        $this->mark = [];

        $this->add('default');
    }

    private function markAdd($item) {
        if (!isset($this->mark[$item])) {
            // confirm existance key, and check for requires
            $cfg = $this->config;
            if (!isset($cfg->$item)) {
                return;
                //TODO: Log
                //throw new \Exception("Asset key $item not in configuration");
            }
            $asset = $cfg->$item;
            if (isset($asset['requires'])) {
                $this->add($asset['requires']);
            }
            $this->order[] = $item;
            $this->mark[$item] = true;
        }
    }

    public function add($list) {
        if (is_array($list)) {
            foreach ($list as $value) {
                $this->markAdd($value);
            }
        } else if (is_string($list)) {
            $this->markAdd($list);
        }
    }

    public function Link() {
        $outs = '';
        if (!empty($this->order)) {
            foreach ($this->order as $name) {
                $outs .= $this->LinkPut($name);
            }
            return $outs;
        } else {
            return '';
        }
    }

    /** Link attributes not substituted
     * 
     * @param type $name
     * @return string
     */
    protected function LinkPut($name) {
        $cfg = $this->config;
        $outs = "";
        if (isset($cfg->$name)) {
            $assets = $cfg->$name;
            if (isset($assets['link'])) {
                $outs .= "<link";
                foreach ($assets['link'] as $attr => $val) {
                    $outs .= " $attr=\"$val\"";
                }
                $outs .= ">" . PHP_EOL;
            }
        }
        //if (empty($outs)) {
        //$outs = "<!-- No link assets found: {" . $name . "} -->" . PHP_EOL;
        //}
        return $outs;
    }
    static public function link_css($path) {
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $path . "\">" . PHP_EOL;
    }
    
    /** asset minify cache as relative path to web root */
    public function getCache() {
        return  '/' . $this->f3->theme . '/cache';
    }
    public function clearCache() {
         \WC\Dos::rm_all( @\glob($this->web . $this->getCache() . '/*') );
    }
    public function CssMinify() {
        $result = $this->getCache() . '/' . $this->minify_name . "_min.css";
        $target = $this->web . $result;
        if (file_exists($target)) {
            if (is_file($target) && (time() - filemtime($target) < static::MAX_AGE)) {
                return static::link_css($result);
            }
        }
        $mini = null;
        $cfg = $this->config;
        
        foreach ($this->order as $name) {
            if (!empty($cfg->$name)){
                $assets = $cfg->$name;
                if (!empty($assets['css'])) {
                    foreach ($assets['css'] as $hpath) {
                        $path = $webroot . $this->unhive($hpath);
                        if (is_null($mini)) {
                            $mini = new Minify\CSS($path);
                        } else {
                            $mini->add($path);
                        }
                    }
                }
            }
        }
        if (!is_null($mini)) {
            $mini->minify($target);
            return static::link_css($result);
        }
    }

    public function CssHeader() {
        if ($this->minify) {
            return $this->CssMinify();
        }
        $outs = '';
        if (!empty($this->order)) {
            foreach ($this->order as $name) {
                $outs .= $this->CssPut($name);
            }
            return $outs;
        } else {
            return '';
        }
    }

    protected function CssPut($name) {
        $cfg = $this->config;
        $outs = "";
        if (isset($cfg->$name)) {
            $assets = $cfg->$name;
            if (isset($assets['css'])) {
                foreach ($assets['css'] as $hpath) {
                    $path = $this->unhive($hpath);
                    $outs .= static::link_css($path);
                }
            }
        }
        //if (empty($outs)) {
        //    $outs = "<!-- No CSS assets found: {" . $name . "} -->" . PHP_EOL;
        //}
        return $outs;
    }

    /**
     * Replace @var1  substitutions in paths
     */
    protected function unhive($hpath) {
        $f3 = $this->f3;
        $path = preg_replace_callback('|@([a-zA-Z][\w\d]*)|',
                function($matches) use ($f3) {
            $subs = $f3->$matches[1];
            return $subs;
        }
                , $hpath, 1
        );
        return $path;
    }

    static function script_js($path) {
        return "<script charset=\"UTF-8\" type=\"text/javascript\" src=\"" . $path . "\"></script>" . PHP_EOL;
    }
    protected function JsPut($name) {
        $cfg = $this->config;
        $outs = "";
        if (isset($cfg->$name)) {
            $assets = $cfg->$name;
            if (isset($assets['js'])) {
                foreach ($assets['js'] as $hpath) {
                    $path = $this->unhive($hpath);
                    $outs .= static::script_js($path);
                }
            }
        }
        //if (empty($outs)) {
        //    $outs = "<!-- No JS assets found: {" . $name . "} -->" . PHP_EOL;
        //}
        return $outs;
    }

    public function JsMinify() {
        $result = $this->getCache() . DIRECTORY_SEPARATOR . $this->minify_name . "_min.js";
        $target = $this->web . $result;
        if (file_exists($target)) {
            if (is_file($target) && (time() - filemtime($target) < static::MAX_AGE)) {
                return static::script_js($result);
            }
        }
        
        $mini = null;
        $cfg = $this->config;
        
        foreach ($this->order as $name) {
            if (!empty($cfg[$name])) {
                $assets = $cfg[$name];
                if (!empty($assets['js'])) {
                    foreach ($assets['js'] as $hpath) {
                        $path = $this->web . '/' . $this->unhive($hpath);
                        if (is_null($mini)) {
                            $mini = new Minify\JS($path);
                        } else {
                            $mini->add($path);
                        }
                    }
                }
            }
        }
        if (!is_null($mini)) {
            $mini->minify($target);
            return static::script_js($result);
        }
    }
    public function JsFooter() {
         if ($this->minify) {
            return $this->JsMinify();
        }
        $outs = '';
        if (!empty($this->order)) {
            foreach ($this->order as $name) {
                $outs .= $this->JsPut($name);
            }
        }
        $outs .= $this->InlineJS();
        return $outs;
    }

    /**
     * InlineJS is dynamic, by generated view templates, after everything
     * else, in order of template execution
     */
    protected function InlineJS() {
        if (empty($this->blobs))
            return '';
        $outs = '';
        foreach ($this->blobs as $js) {
            $outs .= $js;
        }
        return $outs;
    }

    /**
     * Add Inline Blob
     */
    public function addJS($blob) {
        $this->blobs[] = $blob;
    }

}
