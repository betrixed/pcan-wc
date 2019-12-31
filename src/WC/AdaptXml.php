<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC;

/**
 * WConfig can use makeClass to hack the 
 * mutation of classes by overriding 
 * makeClass declared in XmlPhp
 *
 * @author michael
 */
class AdaptXml extends XmlPhp {

    public $orig;
    public $adapt;

    public function __construct($orig, $adapt) {
        $this->orig = $orig;
        $this->adapt = $adapt;
    }

    public function makeClass($c) {
        $c = str_replace($this->orig, $this->adapt, $c);
        return new $c();
    }

}
