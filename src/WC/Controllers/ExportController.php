<?php

/**
 * Isolate blog export and import actions
 *
 * @author Michael Rynn
 */

namespace WC\Controllers;

use WC\Link\BlogExport as Export;
use WC\Link\BlogView;
use WC\Valid;
use WC\UserSession;
use WC\Models\Blog;

class ExportController extends BaseController {

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;

    private $url = "/admin/blog/";

    public function getAllowRole() {
        return 'Admin';
    }

    public function exportpostAction() {
        $post = $_POST;
        $op = Valid::toInt($post, 'bksel');
        $list = [];
        // blog row  is "op" .  substr($name,2)
        foreach ($post as $name => $value) {
            if (substr($name, 0, 2) === 'op') {
                $list[] = intval(substr($name, 2));
            }
        }
        if (!empty($list) && $op !== Export::NOOP) {
            $app = $this->app;
            $cfg = $app->get_secrets();

            $bup = $cfg['backups'];
            $path = $app->SITE_DIR . DIRECTORY_SEPARATOR . $bup['path'] . DIRECTORY_SEPARATOR;
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            if ($op === Export::BACKUP) {
                foreach ($list as $id) {
                    $pack = Export::export($id, $path);
                }
            } else if ($op === Export::DELETE) {
                // replace existing backup
            } else if ($op === Export::BACKUP_DELETE) {
                foreach ($list as $id) {
                    $pack = Export::export($id, $path);
                    $pack = Export::fullDelete($id);
                }
            }
        }

        $args = Valid::toStr($post, 'args');
        $url = empty($args) ? $this->url : $this->url . "?" . $args;
        UserSession::reroute($url);
    }

    public function exportAction() {
        $view = $this->getView();
        $m = $view->m;
       
        BlogView::pageFromRequest($m);
         $m->url = $this->url;
        return $this->render('blog', 'export');
    }

    public function importpostAction() {
        $post = $_POST;
        $op = Valid::toInt($post, 'bksel', 0);
        $list = [];
        foreach ($post as $name => $value) {
            if (substr($name, 0, 3) === 'op-') {
                $list[] = $value;
            }
        }
        $app = $this->app;
        $imp = $app->get_secrets('imports');
        $site = $app->SITE_DIR . "/";
        $imp_path = $site . $imp['path'];
        $bup = $app->get_secrets('backups');
        $bup_path = $site . $bup['path'];
        $op = Valid::toInt($post, 'bksel', 0);
        if (empty($list)) {
            UserSession::flash("No items were selected");
        }
        foreach ($list as $fname) {
            $import = $imp_path . DIRECTORY_SEPARATOR . $fname;
            $archive = $bup_path . DIRECTORY_SEPARATOR . $fname;
            if ($op === Export::IMPORT_ARCHIVE) {
                $blog = json_decode(file_get_contents($import), true);
                $n = new Blog();
                Export::insertPackage($n, $blog, "create");
                rename($import, $archive);
            } else if ($op === Export::IMPORT_UPDATE) {
                $blog = json_decode(file_get_contents($import), true);
                $n = Blog::findFirstByTitleClean($blog['title_clean']);
                if (!empty($n)) {
                    Export::export($n->id);
                    $dbop = "update";
                } else {
                    $n = new Blog();
                    $dbop = "create";
                }
                Export::insertPackage($n, $blog, $op);
            } else if ($op === Export::IMPORT_MOVE) {
                rename($import, $archive);
            }
        }
              
        UserSession::reroute($this->url . 'import');
    }

    public function importAction() {
        $app = $this->app;
        $bup = $app->get_secrets('imports');

        $path = $app->SITE_DIR . "/" . $bup['path'];
        $dh = opendir($path);
        $packs = [];
        $log = [];

        while (true) {
// create a list of entries if image type
            $entry = readdir($dh);
            if ($entry === false) {
                break;
            }
            $ext = pathinfo($entry, PATHINFO_EXTENSION);
            if ($ext == 'json') {
                $pack = json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . $entry), true);
                $pack['file'] = $entry;
                $blog = $pack['blog'];
                $version = $pack['version'];
                if (empty($version)) {
                    $compare = "Empty Version";
                } else if (floatval($version) < 0.2) {
                    $compare = "Version $version";
                } else {
                    $compare = "New";
                }
                // check if matching title clean exists, get its id, date_updated & published
                $title_clean = $blog['title_clean'];

                $match = Blog::findFirstByTitleClean($title_clean);
                if (!empty($match)) {
                    $pack['match'] = $match;
                    $current = $match->date_updated;
                    $import = $blog['date_updated'];
                    if ($import > $current) {
                        $compare = "Newer";
                    } else if ($import === $current) {
                        $compare = "Same";
                    } else if ($import < $current) {
                        $compare = "Older";
                    }
                } else {
                    $pack['match'] = null;
                }
                $pack['compare'] = $compare;
                $packs[] = $pack;
            }
        }
        closedir($dh);
        $view = $this->getView();
        $m = $view->m;
        $m->packs = $packs;
        $m->args = "";
        $m->title = "Import";
        $m->path = $path;
        $m->url = $this->url;
        return $this->render('blog', 'import');
    }

}
