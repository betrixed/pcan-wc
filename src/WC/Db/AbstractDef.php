<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Db;

/**
 * Description of AbstractSchemaDef
 *
 * @author michael
 */
abstract class  AbstractDef extends \WC\NameDef {
    abstract public function generate( $script, $stages);
}
