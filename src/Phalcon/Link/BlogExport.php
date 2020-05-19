<?php

namespace App\Link;

use WC\Server;
use WC\DbQuery;
use WC\App;
use App\Models\Blog;
use Phalcon\Db\Column;
use App\Link\BlogView;
use Masterminds\HTML5;

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
        //$search = ["<figure", "</figure", "<figcaption" , "</figcaption"];
        //$replace = ["<div", "</div", "<div", "</div"];
        //$html = str_replace($search, $replace, $article);
        //$old_error_handler = set_error_handler("HTML_ERROR");
        //libxml_use_internal_errors(true);
        $result = [];
        try {
            $parser = new HTML5();
            $app = App::instance();
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
        $version = floatval($pack['version']);
        if ($version < 0.2) {
            return false;
        }
        $rec = $pack['blog'];
        $blog->title = $rec['title'];
        $blog->title_clean = $rec['title_clean'];
        $blog->article = $rec['article'];
        $blog->date_published = $rec['date_published'];
        $blog->date_updated = $rec['date_updated'];
        $blog->style = $rec['style'];
        $blog->issue = $rec['issue'];

        $db = Server::db();
        if (!empty($blog->id)) {
            $this->update();
        } else {
            $this->create();
        }
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
                            ['bid' => Column::BIND_PARAM_INT,
                                'mid' => Column::BIND_PARAM_INT,
                                'ct' => Column::BIND_PARAM_STR]);
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
                        ['slug' => Column::BIND_PARAM_STR]);
                if (!empty($catid)) {
                    $db->execute("insert into blog_to_category(blog_id, category_id) values (:b1, :c1)",
                            ["b1" => $blogid, "c1" => $catid[0]['id']],
                            ["b1" => Column::BIND_PARAM_INT, "c1" => Column::BIND_PARAM_INT]
                    );
                }
            }
        }
        $images = $pack['images'];

        if (!empty($images)) {
            $app = App::instance();
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
        $blog = Blog::findFirstById($id);
        // get essential data for json, not by keys of this DB

        $pack = [];
        $pack['version'] = "0.2";
        $rec = [];
        $rec['title'] = $blog->title;
        $rec['title_clean'] = $blog->title_clean;
        $rec['article'] = $blog->article;
        $rec['date_published'] = $blog->date_published;
        $rec['date_updated'] = $blog->date_updated;
        $rec['style'] = $blog->style;
        $rec['issue'] = $blog->issue;

        $file_date = (new \DateTime($rec['date_updated']))->format('d-M-y');
        $file_name = $rec['title_clean'] . "_" . $file_date . ".json";

        $pack['packname'] = $file_name;
        $secrets = App::instance()->get_secrets();

        $pack['image_domain'] = $secrets['backups']['image_domain'];

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
