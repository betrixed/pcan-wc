<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC;


/**
 * Compatibility extensions for Plates
 *
 * @author michael
 */
class ViewEngine extends \Phalcon\Mvc\View\Engine\Php
{
    public function layout($vpath, $data) {
        // negation, do nothing
    }
}
