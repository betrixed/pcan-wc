<?php

namespace WC\Mixin;
/**
 * HtmlPurifier functionality using method purify
 */
trait HtmlPurify {
    
    protected $purify_obj = null;
    
    public function purify(array $req, string $ix) : string
    {
        if (!isset($req[$ix])) {
            return  "";
        }
        if (!$this->purify_obj) {
            $app = $this->app;
            require_once  $app->php_dir  . '/lib/ezyang/HTMLPurifier.auto.php';
            $config = \HTMLPurifier_Config::createDefault();
            $this->purify_obj = new \HTMLPurifier($config);
        }
        return $this->purify_obj->purify($req[$ix]);
    } 
}
