<?php

namespace WC;

/**
 * Organise typical webpage .css, .js assets
 */
class Assets extends \Prefab {

    private $mark;
    private $order;
    private $loggedIn;
    private $assetSrc;
    private $assetProd;
    private $blobs;
    private $web;
    private $f3;
    private $config;

    static public function registerAssets($assets) {
        $si = static::instance();
        $si->addAssets($assets);
    }

    public function addAssets($cfg) {
        $this->config = array_merge($this->config, $cfg);
    }

    public function __construct() {
        $this->f3 = \Base::Instance();
        $f3 = $this->f3;
        $path = $f3->get('sitepath') . "assets.xml";
        if (!file_exists($path)) {
            $path = $f3->get('php') . "config/assets.xml";
        }
        $this->web = $f3->get('webDir');

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
            if (!isset($cfg[$item])) {
                return;
                //TODO: Log
                //throw new \Exception("Asset key $item not in configuration");
            }
            $asset = $cfg[$item];
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
        if (isset($cfg[$name])) {
            $assets = &$cfg[$name];
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

    public function CssHeader() {
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
        if (isset($cfg[$name])) {
            $assets = &$cfg[$name];
            if (isset($assets['css'])) {
                foreach ($assets['css'] as $hpath) {
                    $path = $this->unhive($hpath);
                    $outs .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $path . "\">" . PHP_EOL;
                }
            }
        }
        if (empty($outs)) {
            $outs = "<!-- No CSS assets found: {" . $name . "} -->" . PHP_EOL;
        }
        return $outs;
    }

    protected function unhive($hpath) {
        $f3 = $this->f3;
        $path = preg_replace_callback('|@(\w[\w\d]*)|',
                function($matches) use ($f3) {
            $subs = $f3->get($matches[1]);
            return $subs;
        }
                , $hpath, 1
        );
        return $path;
    }

    protected function JsPut($name) {
        $cfg = $this->config;
        $outs = "";
        if (isset($cfg[$name])) {
            $assets = &$cfg[$name];
            if (isset($assets['js'])) {
                foreach ($assets['js'] as $hpath) {
                    $path = $this->unhive($hpath);
                    $outs .= "<script charset=\"UTF-8\" type=\"text/javascript\" src=\"" . $path . "\"></script>" . PHP_EOL;
                }
            }
        }
        if (empty($outs)) {
            $outs = "<!-- No JS assets found: {" . $name . "} -->" . PHP_EOL;
        }
        return $outs;
    }

    public function JsFooter() {
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
