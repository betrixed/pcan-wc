<?php

namespace WC\Db;

use ActiveRecord\{ConnectionManager, Config, Connection};
use WC\Valid;

class ARModels {
   
    /**
     * Return camel case of lower case underscore separated name
     * @param string $uname
     */
    static public function ccuName(string $uname) {
        return str_replace(' ','', ucwords(str_replace('_', ' ', $uname)));
    }
    
    static public function makeModelFiles(string $dbconfig_name, string $folder, \Closure $output=null) {
            global $APP;
            
            $db = ConnectionManager::get_connection($dbconfig_name);
            $tables = $db->query_for_tables();
            $eol = PHP_EOL;
            $eol2 = PHP_EOL . PHP_EOL;
            $tab = "    ";
            $mappings = require $APP->site_dir . "/schema/field_maps.php";
            $comment = "/** Generated from '$dbconfig_name' " . Valid::now() . " */" . $eol;
            foreach($tables as $model) {
                if (!empty($mappings)) {
                    $fmap = $mappings[$model] ?? null;
                }
                else {
                    $fmap = null;
                }
                
                $class_name = self::ccuName($model);
                $file_path = $folder . "/" . $class_name . ".php";
                
                if (!file_exists($file_path)) {
                    if ($output !== null) {
                        $output($file_path);
                    }
                    $cf = "<?php" . $eol;
                    $cf .= $comment;
                    $cf .= "namespace WC\\Models;" . $eol2;
                    $cf .= "use ActiveRecord\\Model;" . $eol2;
                    $cf .= "class " . $class_name . " extends Model" . $eol;
                    $cf .= "{" . $eol;
                    $cf .= $tab . "static \$table_name = '" . $model . "';" . $eol2;
                    
                    if (!empty($fmap)) {
                        foreach($fmap as $access => $property) {
                            
                            $cf .= $tab . "function set_" . $access . "(\$val)" . $eol;
                            $cf .= $tab . "{" . $eol . $tab . $tab . "\$this->" . $property . " = \$val;" . $eol;
                            $cf .= $tab . "}" . $eol2;
                            $cf .= $tab . "function get_" . $access . "()" . $eol;
                            $cf .= $tab . "{" . $eol . $tab . $tab . "return \$this->" . $property . ";" . $eol;
                            $cf .= $tab . "}" . $eol2;
                        }
                    }
                    $cf .= "}" . $eol;
                    file_put_contents($file_path, $cf);
                }
            }
    }
}
