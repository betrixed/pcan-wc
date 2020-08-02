<?php

namespace WC\Mixin;

/**
 * HtmlPurifier functionality using method purify
 */
trait HtmlPurify
{

    protected $purify_obj = null;

    public function purify(array $req, string $ix): string
    {
        if (!isset($req[$ix])) {
            return "";
        }
        /** currently no solution here. HtmlPurifier may do some horrible things */
        /** Html Tidy not recommended either
         If some solution comes up, this is where it will go

        if (!$this->purify_obj) {
            $app = $this->app;
            require_once $app->php_dir . '/lib/ezyang/HTMLPurifier.auto.php';
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'a[href],blockquote,br,del,em,figcaption,figure,h1,h2,h3,h4,h5,h6,img[title|alt|src],li,ol,p,pre,strong,ul');
            $config->set('HTML.DefinitionID', 'enduser-customize.html tutorial');
            $config->set('HTML.DefinitionRev', 5);
            if ($def = $config->maybeGetRawHTMLDefinition()) {
                $def->addElement('figcaption', 'Block', 'Flow', 'Common');
                $def->addElement('figure', 'Block', 'Flow', 'Common');
            }
            $this->purify_obj = new \HTMLPurifier($config);
        }
        return $this->purify_obj->purify($req[$ix]);
         * 
         */
        return $req[$ix];
    }

}
