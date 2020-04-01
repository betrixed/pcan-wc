<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Db;

/**
 * @author michael
 */
interface IQuery
{
    public function getSchemaName() : string;
    
    public function cursor(string $sql, array $params = null);
    
    function queryAll(string $sql, array $params = null) : array;
    
    
}
