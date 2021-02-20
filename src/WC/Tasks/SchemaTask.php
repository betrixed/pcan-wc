<?php

namespace WC\Tasks;
//use WC\Db\ARModels;
use Phalcon\Cli\Task;
use WC\PdocReader;
use WC\Db\Script;
use ActiveRecord\Table;

class SchemaTask extends Task {
    
    private function getSchemaDir() {
        if (empty($this->schema_dir)) {
            $this->schema_dir = $this->app->schema_dir;
        }
        return $this->schema_dir;
    }
    
    function tableAction(array $params) {
        $config_name = $params[0];
        $table_name = $params[1];
        
        $server = $this->server;
        $db = $server->db($config_name);
        
        $table = new Table($table_name);
        foreach($table->columns as $c) {
            echo print_r($c, true) . PHP_EOL;
        }
        
        
    }
    function printAction(array $params) {
        $version = $params[0];
        $schemaDir = $this->getSchemaDir() . DIRECTORY_SEPARATOR;
        $path = $schemaDir . $version . '.schema';
        $cfg = PdocReader::fromFile($path);
        $script = new Script();

        $cfg->generate($script, ['tables' => 'create', 'auto_inc' => true]);

        $cfg->generate($script, ['alter' => true, 'indexes' => true ]);

        echo $script;
    }
    
    function readAction(array $params) {
        global $gCamelize;
        
        $config_name = $params[0];
        $version = $params[1];
        
        $server = $this->server;
        $config = $server->getConfig($config_name);
        $adapter = $config['adapter'];
        
        echo "config " . print_r($adapter, true) . PHP_EOL;
        
        $db = $server->db($config_name);
        $dbschema = 'WC\\' . $gCamelize($adapter) . '\SchemaDef';
        
        
        $schema = new $dbschema();
        $schema->setName($config['dbname']);
        
        $schema->readSchema($db);
        $schema->setName($version);

        $schemaDir = $this->getSchemaDir() . DIRECTORY_SEPARATOR;

        $path = $schemaDir . $version . '.schema';

        $schema->toFile($path);

        $exportData = true;

        if ($exportData) {
            $folderName = $schemaDir . $version . '_dir';
            if (!file_exists($folderName)) {
                mkdir($folderName);
            }

            foreach ($schema->tables as $tdef) {
                $tdef->exportDataToCSV($schema->newQuery($db), $folderName . '/' . $tdef->name . '.csv');
            }
        }
    }
}

