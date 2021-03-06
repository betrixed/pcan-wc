<?php

namespace WC;

use MatthiasMullie\Minify;

/**
 * Organise typical webpage .css, .js assets
 */
class Assets
{

    private $warn_missing;
    private $render_lock;
    private $mark;
    private $order;
    private $loggedIn;
    private $assetSrc;
    private $assetProd;
    private $blobs;
    private $web;
    private $app;
    private $config;
    private $minify;
    private $minify_name;
    private $src_paths;

    // Minify cache
    const MAX_AGE = 60 * 60 * 24;
    const SCRIPT_TAG = '<script>' . PHP_EOL;
    const SCRIPT_END = '</script>' . PHP_EOL;

    public function minify($name)
    {
        $this->minify_name = $name;
        $this->minify = true;
    }

    public function addAssets(array $cfg)
    {
        if (is_array($this->config)) {
            $this->config = array_merge($this->config, $cfg);
        } else { // is_object
            foreach ($cfg as $key => $value) {
                $this->config->$key = $value;
            }
        }
    }

    public function __construct(object $app)
    {
        $this->warn_missing = true;
        $this->render_lock = false;
        $this->app = $app;
        $cfg = WConfig::fromXml($app->site_dir . "/assets.xml");
        $this->config = $cfg;
        $this->web = $app->web_dir;
        $this->assetSrc = $cfg->assetSrc;
        $this->assetProd = $app->temp_dir . $cfg->assetCache;
        $this->order = [];
        $this->mark = [];
        $this->add('default');
        if (isset($app->theme)) {
            $srcpaths[] = $app->web_dir . "/" . $app->theme;
        }
        $srcpaths[] = $app->site_dir . "/web";
        $srcpaths[] = $app->pcan_dir . "/web";
        $this->src_paths = $srcpaths;
    }

    private function markAdd($item)
    {
        if (!isset($this->mark[$item])) {
            // confirm existance key, and check for requires
            $cfg = $this->config;
            if (!isset($cfg->$item)) {
                throw new \Exception("Asset key $item not in configuration");
                //TODO: Log
                //
            }
            $asset = $cfg->$item;
            if (isset($asset['requires'])) {
                $this->add($asset['requires']);
            }
            $this->order[] = $item;
            $this->mark[$item] = true;
        }
    }

    public function add($list)
    {
        if ($this->render_lock) {
            throw new \Exception('Assets are locked with view rendering');
        }
        if (is_array($list)) {
            foreach ($list as $value) {
                $this->markAdd($value);
            }
        } else if (is_string($list)) {
            $this->markAdd($list);
        }
    }

    public function Link()
    {
        $this->render_lock = true;
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
    protected function LinkPut($name)
    {
        $this->render_lock = true;
        $cfg = $this->config;
        $outs = "";
        if (isset($cfg->$name)) {
            $assets = &$cfg->$name;
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

    public function inline_css($name): string
    {
        $cfg = $this->config;
        $outs = "";
        if (isset($cfg->$name)) {
            $assets = &$cfg->$name;
            if (isset($assets['css'])) {
                foreach ($assets['css'] as $hpath) {
                    $css_path = $this->unhive($hpath);                    
                    $path = $this->findSourceFile($css_path);
                    if (!empty($path)) {
                        $outs .= PHP_EOL . '<style>' . PHP_EOL;
                        $outs .= file_get_contents($path);
                        $outs .= PHP_EOL . '</style>' . PHP_EOL;
                    } else {
                        throw new Exception("inline asset missing: " . $css_path);
                    }
                }
            }
        }
        return $outs;
    }

    static public function link_css($path)
    {
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $path . "\">" . PHP_EOL;
    }

    /** asset minify cache as relative path to web root */
    public function getAssetCache()
    {
        $cache = '/' . $this->app->theme . '/cache';
        $path = $this->web . $cache;
        if (!is_dir($path)) {
            Dos::makedir($path);
        }
        return $cache;
    }

    public function clearCache()
    {
        \WC\Dos::rm_all(@\glob($this->web . $this->getAssetCache() . '/*'));
    }

    public function CssMinify()
    {
        $this->render_lock = true;
        $result = $this->getAssetCache() . '/' . $this->minify_name . "_min.css";
        $target = $this->web . $result;
        if (file_exists($target)) {
            if (is_file($target) && (time() - filemtime($target) < static::MAX_AGE)) {
                return static::link_css($result);
            }
        }
        $mini = null;
        $cfg = $this->config;

        foreach ($this->order as $name) {
            if (!empty($cfg->$name)) {
                $assets = $cfg->$name;
                if (!empty($assets['css'])) {
                    foreach ($assets['css'] as $hpath) {
                        $path = $this->web . $this->unhive($hpath);
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

    public function CssHeader()
    {
        $this->render_lock = true;
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

    protected function verify($path)
    {
        if (substr($path, 0, 1) === '/') {
            $webpath = $this->web . $path;
            if (!file_exists($this->web . $path)) {
                trigger_error("file $webpath not found");
            }
        }
    }

    protected function unhive($hpath) {
        return $this->app->replace_in($hpath);
    }
    protected function CssPut($name)
    {
        $this->render_lock = true;
        $cfg = $this->config;
        $outs = "";
        if (isset($cfg->$name)) {
            $assets = $cfg->$name;
            if (isset($assets['css'])) {
                foreach ($assets['css'] as $hpath) {
                    $path = $this->unhive($hpath);
                    if ($this->warn_missing) {
                        $this->verify($path);
                    }
                    $outs .= static::link_css($path);
                }
            }
        }
        //if (empty($outs)) {
        //    $outs = "<!-- No CSS assets found: {" . $name . "} -->" . PHP_EOL;
        //}
        return $outs;
    }

    static function script_js($path)
    {
        return "<script charset=\"UTF-8\" type=\"text/javascript\" src=\"" . $path . "\"></script>" . PHP_EOL;
    }

    public function findSourceFile($fpath): string
    {
        foreach ($this->src_paths as $src) {
            $test = $src . $fpath;
            if (file_exists($test)) {
                return $test;
            }
        }
        return '';
    }

    protected function JsPut($name)
    {
        $this->render_lock = true;
        $cfg = $this->config;
        $outs = "";
        if (isset($cfg->$name)) {
            $assets = $cfg->$name;

            if (isset($assets['js'])) {
                foreach ($assets['js'] as $hpath) {
                    $path = $this->unhive($hpath);
                    if ($this->warn_missing) {
                        $this->verify($path);
                    }
                    $outs .= static::script_js($path);
                }
            }
            if (isset($assets['js-inline'])) {
                foreach ($assets['js-inline'] as $srctype => $list) {
                    foreach ($list as $jspath) {
                        $path = $this->findSourceFile($jspath);
                        if (!empty($path)) {
                            $script = file_get_contents($path);
                            $this->addJS(static::SCRIPT_TAG . $script . static::SCRIPT_END);
                        } else {
                            throw new Exception("inline asset missing: " . $jspath);
                        }
                    }
                }
            }
        }
        //if (empty($outs)) {
        //    $outs = "<!-- No JS assets found: {" . $name . "} -->" . PHP_EOL;
        //}
        return $outs;
    }

    public function JsMinify()
    {
        $this->render_lock = true;
        $result = $this->getAssetCache() . DIRECTORY_SEPARATOR . $this->minify_name . "_min.js";
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

    public function JsFooter()
    {
        $this->render_lock = true;
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
     * Called by JsFooter.
     * InlineJS is dynamic, blobs of html script by generated view templates, after everything
     * else, in order of template execution
     */
    protected function InlineJS()
    {
        $this->render_lock = true;
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
    public function addJS($blob)
    {
        $this->blobs[] = $blob;
    }

}
