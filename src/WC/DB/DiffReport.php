<?php
namespace WC\DB;

use WC\App;

class DiffReport {
    public $log;
    public $diffcount;
    public $sch1;
    public $sch2;
    
    public function __construct() {
        $this->log = '';
        $this->diffcount = 0;
    }
    
    // fake DB interface by logging
    public function exec($sql, $param = []) {
        if (!empty($param)) {
            foreach($param as $key => $value) {
                $mkey = ($key[0] != ':') ? ":" . $key : $key;
                if (is_string($value)) {
                    $value = \PDO::quote($value);
                }
                $sql = str_replace($mkey,$value,$sql);
            }
        }
        $this->log($sql);
    }
    public function inc() {
        $this->diffcount++;
    }
    public function log(string $s, $inc = true) {
        $warn = $inc ? '- ' : '';
        $this->log .= $warn . $s . PHP_EOL;
        if ($inc) {
            $this->diffcount++;
        }
    }
    
    public function doCompareColumns($cdef1, $cdef2)
    {
        $d1 = $cdef1;
        $d2 = $cdef2;
        
        $common = [];
        foreach($d1 as $key => $value) {
            if (!isset($d2->$key)) {
                $this->log('Column ' . $cdef2->getName() . ' does not have property ' . $key . ' = ' . $value);
            }
            else {
                $common[] = $key;
            }
        }
        foreach($d2 as $key => $value) {
            if (!isset($d1->$key)) {
                $this->log('Column ' . $cdef1->getName() . ' does not have property ' . $key . ' = ' . $value);
            }
        }
        foreach($common as $key) {
            if ($d1->$key !== $d2->$key) {
                $this->log('Property not same for ' . $key . ': ' . $d1->$key . ' and ' . $d2->$key );
            }
        }
    }
    
    
    public function doCompareIndexes($cdef1, $cdef2)
    {
        $d1 = $cdef1;
        $d2 = $cdef2;
        foreach($d1 as $key => $value) {
            if (!isset($d2[$key])) {
                $this->log('Index ' . $cdef2->getName() . ' does not have property ' . $key . ' = ' . $value);
            }
            else {
                $common[] = $key;
            }
        }
        foreach($d2 as $key => $value) {
            if (!isset($d1[$key])) {
                $this->log('Index ' . $cdef1->getName() . ' does not have property ' . $key . ' = ' . $value);
            }
        }
        foreach($common as $key) {
            if ($d1[$key] !== $d2[$key]) {
                $this->log('Property not same for ' . $key . ': ' . $d1[$key] . ' and ' . $d2[$key] );
            }
        }
        
    }
    public function compareColumnArrays($tdef1, $tdef2)
    {
        $common = [];
        
        $c1 = $tdef1->columns;
        $c2 = $tdef2->columns;
        
        foreach($c1 as $name => $f1) {
            if (!isset($c2[$name])) {
                $this->log('Column ' . $name . ' not found in table ' . $tdef1->name);
            }
            else {
                $common[] = $name;
            }
        }
        foreach($c2 as $name => $f2) {
            if (!isset($c1[$name])) {
                $this->log('Column ' . $name . ' not found in table ' . $tdef1->name);
            }
        }
        
        foreach($common as $name) {
            $this->doCompareColumns($c1[$name], $c2[$name]);
        }
    }
    
    public function doCompareConstraint($cdef1, $cdef2)
    {
        $d1 = $cdef1;
        $d2 = $cdef2;
        foreach($d1 as $key => $value) {
            if (!isset($d2[$key])) {
                $this->log('Constraint ' . $cdef2->getName() . ' does not have property ' . $key . ' = ' . $value);
            }
            else {
                $common[] = $key;
            }
        }
        foreach($d2 as $key => $value) {
            if (!isset($d1[$key])) {
                $this->log('Constraint ' . $cdef1->getName() . ' does not have property ' . $key . ' = ' . $value);
            }
        }
        foreach($common as $key) {
            if ($d1[$key] !== $d2[$key]) {
                $this->log('Constraint Property not same for ' . $key . ': ' . $d1[$key] . ' and ' . $d2[$key] );
            }
        }
        
    }
     public function compareIndexArrays($tdef1, $tdef2)
    {
        $common = [];
        
        $c1 = $tdef1->indexes;
        $c2 = $tdef2->indexes;
        
        foreach($c1 as $name => $f1) {
            if (!isset($c2[$name])) {
                $this->log('Index ' . $name . ' not found in table ' . $tdef2->name);
            }
            else {
                $common[] = $name;
            }
        }
        foreach($c2 as $name => $f2) {
            if (!isset($c1[$name])) {
                $this->log('Index ' . $name . ' not found in table ' . $tdef1->name);
            }
        }
        
        foreach($common as $name) {
            $this->doCompareIndexes($c1[$name], $c2[$name]);
        }
    }
    public function compareOptions($tdef1, $tdef2)
    {
        $common = [];
        
        $c1 = $tdef1->options;
        $c2 = $tdef2->options;
        
        foreach($c1 as $name => $f1) {
            if (!isset($c2[$name])) {
                $this->log('Option ' . $name . ' not found for table ' . $tdef2->name);
            }
            else {
                $common[] = $name;
            }
        }
        foreach($c2 as $name => $f2) {
            if (!isset($c1[$name])) {
                $this->log('Option ' . $name . ' not found for table ' . $tdef1->name);
            }
        }
        
        foreach($common as $name) {
            if ($c1[$name] !== $c2[$name]) {
                $this->log('Option values differ for ' . $name . ':  ' . $c1[$name] . ' and ' . $c2[$name]);
            }
        }
    }
    public function compareRefArrays($tdef1, $tdef2)
    {
        $common = [];
        
        // references are a constraint name
        $c1 = $tdef1->references;
        $c2 = $tdef2->references;
        
        foreach($c1 as $name) {
            if (!in_array($name,$c2)) {
                $this->log('Constraint ' . $name . ' not found in table ' . $tdef2->name);
            }
            else {
                $common[] = $name;
            }
        }
        foreach($c2 as $name) {
            if (!in_array($name,$c1)) {
                $this->log('Constraint ' . $name . ' not found in table ' . $tdef1->name);
            }
        }
        $c1 = $tdef1->foreignkeys;
        $c2 = $tdef2->foreignkeys;
        foreach($c1 as $name) {
            if (!in_array($name,$c2)) {
                $this->log('Foreign key ' . $name . ' not present ' . $tdef2->name);
            }
            else {
                $common[] = $name;
            }
        }
        foreach($c2 as $name) {
            if (!in_array($name,$c1)) {
                $this->log('Foreign key ' . $name . '  not present  ' . $tdef1->name);
            }
        }
        
        // relay on schema
    }
    public function doCompareTables($tdef1, $tdef2) {
        
        $this->log('Compare tables ' . $tdef1->name . ' and ' . $tdef2->name, false);
        $this->compareColumnArrays($tdef1,  $tdef2);
        $this->compareIndexArrays($tdef1,  $tdef2);
        $this->compareOptions($tdef1,  $tdef2);
    }
    
    
    public function compareConstraints($c1,$c2) {
        $common = [];
        
        
        foreach($c1 as $offset => $prop) {
            if (!isset($c2->$offset)) {
                $this->log('Constraint property ' . $offset . ' not found in ' . $this->sch2->name);
            }
            else {
                $common[] = $offset;
            }
        }
        foreach($c2 as $offset => $prop) {
            if (!isset($c1->$offset)) {
                $this->log('Constraint property ' . $offset . ' not found in ' . $this->sch1->name);
            }
        }
        foreach($common as $offset) {
            if ($offset === 'schema' || $offset = 'p_schema') {
                continue;
            }
            
            if ($c1->$offset !== $c2->$offset) {
                   $this->log( $offset . ' constraint property(1) ' . $c1->$offset . ' differs(2) ' . $c2->$offset );            
            }
        }
    }
    public function compareRelations() {
        /* compare by name */
        $r1 = $this->sch1->relations;
        $r2 = $this->sch2->relations;
        $v1 = $this->sch1->name;
        $v2 = $this->sch2->name;
        $common = [];
        foreach($r1 as $name => $rel) {
            if (isset( $r2[$name])) {
                $common[] = $name;
            }
            else {
                $this->log('Relation ' . $name . ' in ' . $v1 . ' not in ' . $v2 );
            }
        }
        foreach($r2 as $name => $rel) {
            if (!isset( $r1[$name])) {
                $this->log('Relation ' . $name . ' in ' . $v2 . ' not in ' . $v1 );
            }
        }
        if (!empty($common)) {
            foreach($common as $name) {
                $this->compareConstraints($r1[$name], $r2[$name]);
            }
        }
    }
    public function doCompareSchema($s1, $s2)
    {
        $sdir = App::instance()->getSchemaDir();
        
        $this->sch1 = SchemaDef::fromFile($sdir . $s1 . '.schema');
        $this->sch2 = SchemaDef::fromFile($sdir . $s2 . '.schema');

        $this->log('Compare schema files ' . $s1 . ' and ' . $s2, false);
        
        $common = [];
        $db1 = $this->sch1['tables'];
        $db2 = $this->sch2['tables'];
        
        foreach($db1 as $name => $tdef) {
            if (isset($db2[$name]))
            {
                $common[] = $name;
            }
            else {
                $this->log("Table " . $name . " does not exist in " . $s2);
            }
        }
        foreach($db2 as $name => $tdef) {
            if (!isset($db1[$name]))
            {
                $this->log("Table " . $name . " does not exist in " . $s1);
            }
        }         
        foreach($common as $name) {
            $this->doCompareTables($db1[$name], $db2[$name]);
        }
        
        $this->compareRelations();
        
        $this->log($this->diffcount . " differences logged");
    }
}
