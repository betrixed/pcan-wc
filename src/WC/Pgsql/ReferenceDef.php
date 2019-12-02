<?php

/**
 * @author Michael Rynn
 */

namespace WC\Pgsql;
use WC\NameDef;
/**
 * Intermediate type to and from on-file TOML table definition. 
 * Match read and write key names and format.
 */
class ReferenceDef extends NameDef {
    public function setSchema($row) {
        $this->name = $row['fk_constraint_name'];
        $this->table = $row['table'];
        $this->schema = $row['schema'];
        $this->columns = [$row['column_name']];
        $this->p_columns = [$row['p_column_name']];
        $this->p_table = $row['p_table'];
        $this->p_schema = $row['p_schema'];
        $this->on_delete =  $row['delete_rule'];
        $this->on_update =  $row['update_rule'];
    }
    
    public function dropSql() {
        return ' DROP CONSTRAINT IF EXISTS `' .  $this->name .  '`;';
    }
    private function constraintSql() {
        $qt = '';
        $outs = SchemaDef::alterTableSql($this->table);
        $outs .= ' ADD CONSTRAINT ' . $this->name 
                . ' FOREIGN KEY ' . NameDef::name_list($this->columns,$qt)
                . ' REFERENCES ' . $this->p_table . ' '
                . NameDef::name_list($this->p_columns, $qt);
        if (isset($this->on_delete)) {
            $outs .= ' ON DELETE ' . $this->on_delete;
        }
        if (isset($this->on_update)) {
            $outs .= ' ON UPDATE ' . $this->on_update;
        }
        return $outs;
    }
    public function toSql($script, $stage) {
        $script->add($this->constraintSql());
    }
}
