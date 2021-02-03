<?php

namespace WC\Link;

use WC\Server;
use WC\DbQuery;
use WC\App;
use WC\Models\Blog;

use Masterminds\HTML5;
use WC\Link\RevisionOp;
use WC\Link\BlogView;
/**
 * Change a blog article into JSON package.
 *
 * @author Michael Rynn
 */
class BlogExport {

    const NOOP = 0;
    const BACKUP = 1;
    const DELETE = 2;
    const BACKUP_DELETE = 4;
    const IMPORT_ARCHIVE = 1;
    const IMPORT_UPDATE = 2;
    const IMPORT_MOVE = 4;

    static public function backupOptions(): array {
        return [
            static::NOOP => 'Do Nothing',
            static::BACKUP => 'Create backup',
            static::DELETE => 'Delete',
            static::BACKUP_DELETE => 'Backup then delete'
        ];
    }

    static public function importOptions(): array {
        return [
            static::NOOP => 'Do Nothing',
            static::IMPORT_ARCHIVE => 'Import &amp; Archive',
            static::IMPORT_UPDATE => 'Import &amp; Replace',
            static::IMPORT_MOVE => 'Archive the import'
        ];
    }

    // return array, with gallerypaths that reference array of image file names
    static public function imageFiles($article) {
        global $container;
        //$search = ["<figure", "</figure", "<figcaption" , "</figcaption"];
        //$replace = ["<div", "</div", "<div", "</div"];
        //$html = str_replace($search, $replace, $article);
        //$old_error_handler = set_error_handler("HTML_ERROR");
        //libxml_use_internal_errors(true);
        $result = [];
        try {
            $parser = new HTML5();
            $app = $container->get('app');
            $dom = $parser->loadHTML($article);
            $images = $dom->getElementsByTagName('img');

            $defaultGallery = $app->gallery . "/";
            $matches = ["/image/gallery/", "/files/", "/image/"];
            if (array_search($defaultGallery, $matches) === false) {
                $matches[] = $defaultGallery;
            }
            foreach ($images as $img) {
                if ($img->hasAttribute('src')) {
                    $src = $img->getAttribute('src');
                    foreach ($matches as $ix => $dir) {
                        $s = $dir;
                        if (strpos($src, $s) === 0) {
                            $subpath = substr($src, strlen($s));
                            if ($ix === 0) {
                                $pos = strpos($subpath, "/");
                                if ($pos !== false) {
                                    $pos++;
                                    $gallery = substr($subpath, 0, $pos);
                                    $subpath = substr($subpath, $pos);
                                    $s .= $gallery;
                                }
                            }
                            if (!isset($result[$s])) {
                                $result[$s] = [];
                            }
                            $result[$s][] = $subpath;
                        }
                    }
                }
            }
            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Set  $blog record and parts from a import package
     * @param type $blog
     * @param type $pack
     * @param type $op
     * @return boolean
     */
    static function insertPackage($blog, $pack, $op): bool {
        global $container;
        
        $version = floatval($pack['version']);
        if ($version < 0.2) {
            return false;
        }
        $rec = $pack['blog'];
        $blog->title = $rec['title'];
        $blog->title_clean = $rec['title_clean'];
        
        if (!empty($blog->id)) {
            $revision = new BlogRevision();
            $revision->revision = RevisionOp::newRevision($blog);
        }
        else {
            $revision = new BlogRevision();
            $revision->revision = 1;
        }
        $revision->content = $rec['article'];
        //$blog->date_published = $rec['date_published'];
        $revision->date_saved = $rec['date_updated'];
        $blog->style = $rec['style'];
        $blog->issue = $rec['issue'];

        $db = Server::db();
        $blog->revision = $revision->revision;
        
        if (!empty($blog->id)) {
            $blog->update();
        } else {
            $blog->save();
        }
         $revision->blog_id = $blog->id;
         $revision->save();
         
        $blogid = $blog->id;
        if ($op === "update") {
            $db->execute("delete from blog_meta where blog_id = ?", [$blogid]);
        }
        $meta = $pack['meta'];

        if (!empty($meta)) {
            $sql = "select id from meta where meta_name = ?";
            foreach ($meta as $md) {
                $meta_name = $md['meta_id'];
                $idresult = $db->exec([$sql, $meta_name]);
                if ($idresult != false) {
                    $mid = $idresult[0]['id'];
                    $db->execute("insert into blog_meta(blog_id, meta_id, content)"
                            . "values (:bid, :mid, :ct)",
                            ['bid' => $blogid,
                                'mid' => $mid,
                                'ct' => $md['content']
                            ],
                            ['bid' => \PDO::PARAM_INT,
                                'mid' => \PDO::PARAM_INT,
                                'ct' => \PDO::PARAM_STR]);
                }
            }
        }
        $categorys = $pack['categorys'];
        $qry = new DbQuery($db);
        if (!empty($categorys)) {
            foreach ($categorys as $slug => $title) {
                // ensure entry in blog_to_category
                $catid = $qry->arraySet('select bc.id where bc.name_clean = :slug',
                        ['slug' => $slug],
                        ['slug' => \PDO::PARAM_STR]);
                if (!empty($catid)) {
                    $db->execute("insert into blog_to_category(blog_id, category_id) values (:b1, :c1)",
                            ["b1" => $blogid, "c1" => $catid[0]['id']],
                            ["b1" => \PDO::PARAM_INT, "c1" => \PDO::PARAM_INT]
                    );
                }
            }
        }
        $images = $pack['images'];

        if (!empty($images)) {
            $app = $container->get('app');
            $imageRoot = $app->WEB . DIRECTORY_SEPARATOR;

            $domain = isset($pack['image_domain']) ? $pack['image_domain'] : null;
            if (empty($domain)) {
                $images = [];
            }
            foreach ($images as $gallery => $list) {
                $dir = $imageRoot . $gallery;
                foreach ($list as $fname) {
                    $path = $dir . $fname;
                    if (!file_exists($path)) {
                        if (!file_exists($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        $url = $gallery . $fname;
                        $remote = 'http://' . $domain . $url;
                        $content = file_get_contents($remote);
                        if ($content !== false) {
                            file_put_contents($path, $content);
                        }
                    }
                }
            }
        }
        return true;
    }

    static public function package($id): array {
        global $container;
        
        $blog = Blog::findFirstById($id);
        // get essential data for json, not by keys of this DB
        $revision = RevisionOp::linkedRevision($blog);
        if ($revision->revision > 1) {
            $first = RevisionOp::getRevision($blog->id, 1);
        }
        else {
            $first = $revision;
        }
        $pack = [];
        $pack['version'] = "0.2";
        $rec = [];
        $rec['title'] = $blog->title;
        $rec['title_clean'] = $blog->title_clean;
        $rec['article'] = $revision->content;
        $rec['date_published'] = $first->date_saved;
        $rec['date_updated'] = $revision->date_saved;
        $rec['style'] = $blog->style;
        $rec['issue'] = $blog->issue;

        $file_date = (new \DateTime($rec['date_updated']))->format('d-M-y');
        $file_name = $rec['title_clean'] . "_" . $file_date . ".json";

        $pack['packname'] = $file_name;
        $app = $container->get('app');
        $backups = $app->getSecrets('backups');

        $pack['image_domain'] = $backups['image_domain'];

        $catset = BlogView::getCategorySet($id);
        $category = [];
        foreach ($catset->slugs as $ix => $key) {
            $category[$key] = $catset->values[$ix];
        }
        $pack['blog'] = $rec;
        $pack['categorys'] = $category;
        $pack['events'] = BlogView::getEvents('$id');
        $meta = [];
        $metadata = BlogView::getMetaTagHtml($blog->id, $meta);
        $metapack = [];
        foreach ($metadata as $row) {
            $mdata = [];
            $mdata['meta_id'] = $row['meta_name'];
            $mdata['content'] = $row['content'];
            $mdata['data_limit'] = $row['data_limit'];
            $mdata['prefix_site'] = $row['prefix_site'];
            $mdata['display'] = $row['display'];
            $metapack[] = $mdata;
        }
        $pack['meta'] = $metapack;
        $pack['images'] = static::imageFiles($blog->article);

        return $pack;
    }

    static public function export($id, $path) {
        $pack = static::package($id);
        // get essential data for json, not by keys of this DB
        $pack_json = json_encode($pack);
        $fname = $path . $pack['packname'];
        file_put_contents($fname, $pack_json);
        return $fname;
    }

}
