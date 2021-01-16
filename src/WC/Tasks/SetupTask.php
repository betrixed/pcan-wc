<?php
namespace WC\Tasks;
use WC\Link\SiteBuild;
/**
 * Iterative Mail out task from reg_mail table
 *
 * @author michael
 */
class SetupTask extends \Phalcon\Cli\Task 
{
    
    public function dbAction(array $params) {
        $build = new SiteBuild($this);
        $build->buildDatabase($params);
    }
    
    public function fileAction(array $params) {
        $build = new SiteBuild($this);
        $build->buildFiles($params);
    }
}

