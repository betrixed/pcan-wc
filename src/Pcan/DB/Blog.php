<?php

namespace Pcan\DB;

use WC\DB\Server;
/**
 *
 * @author Michael Rynn
 */


class Blog extends \DB\SQL\Mapper {

    static public function hasPrefix($s, $p) {
        $p1 = strlen($p);
        return ($p1 <= strlen($s) && $p1 > 0 && substr($s, 0, $p1) === $p);
    }

    public function __construct($db = null) {
        if (is_null($db)) {
            $db = Server::db();
        }
        parent::__construct($db, 'blog', NULL, 1.0e8); // 100 second
    }

        static public  function getMetaTags($id) {
// setup metatag info
        $sql = "select m.id, m.meta_name,"
                . "m.template, m.data_limit, b.blog_id, b.content"
                . " from meta m"
                . " left join blog_meta b on b.meta_id = m.id"
                . " and b.blog_id = :blogId";
// form with m_attr_value as labels, content as edit text.
        $db =  Server::db();
        $results = $db->exec($sql, ['blogId' => $id]);
        if ($results) {
            return $results;
        }
        else {
            return [];
        }
    }
    /** 
     * Delete related constraints and blog id in one transaction
     * @param type $id
     */
     static public function fullDelete($id) {
         $db = Server::db();
         $db->begin();
         
         $db->exec('delete from blog_meta where blog_id = ?', $id);
         $db->exec('delete from blog_to_category where blog_id = ?', $id);
         $db->exec('delete from event where blogid = ?', $id);
         $db->exec('delete from blog where id = ?', $id);
         
         $db->commit();
         
     }
    static public function getMetaTagHtml($id, &$meta) {
        // setup metatag info
        $sql = <<<EOD
select m.*, b.content
    from meta m
    join blog_meta b on b.meta_id = m.id
    and b.blog_id = ?
    order by meta_name
EOD;
        $db = Server::db();
        $results = $db->exec($sql, $id);
        $f3 = \Base::instance();
        $server = &$f3->ref('SERVER');
        $scheme = $server['REQUEST_SCHEME'];
        $sitePrefix = $scheme . '://' . $f3->get('domain');
    
        if ($results && count($results) > 0) {
            if (is_array($meta)) {
                $meta_tags = [];
                foreach ($results as $row) {
                    $content = str_replace("'", "&apos;", $row['content']);
                    // replace ' with &apos; 
                    
                    if ($row['prefixSite'] && !static::hasPrefix($content, "http")) {
                        if (!static::hasPrefix($content, '/')) {
                            $content = '/' . $content;
                        }
                        $content = $sitePrefix . $content;
                    }
                    $meta_tags[] = str_replace("{}", $content, $row ['template']);
                }
                $meta = $meta_tags;
            }
        } else
            $results = array();
        return $results;
    }
    /**
     * Get all the styles as array[ class ] == (style name)
     */
    static public function &getStyleList() {
        $db = Server::db();
        $styles = $db->exec('select style_class, style_name from blog_style');
        $stylelist = [];
        foreach($styles as $row) {
            $stylelist[   $row['style_class'] ] = $row['style_name'];
        }
        return stylelist;
    }
    // return stdClass with properties cat_blogid, catlist (blog_category records), and string-comma list of 
    // categories.
    static public function getCategorySet($id) {
        $sql = "select c.id, c.name, c.name_clean, b.blog_id from blog_category c"
                . " left outer join blog_to_category b on b.category_id = c.id"
                . " and b.blog_id = :blogId order by c.name";
        $db = Server::db();
        $results = $db->exec($sql, [':blogId' => $id]);
        $catset = new \stdClass();
        $catset->cat_blogid = $id;
        $values = [];
        $slugs = [];
        $available = [];
        $catset->values = &$values;
        $catset->available = &$available;
        $catset->slugs = &$slugs;
        if (!empty($results)) {
            $catset->catlist = &$results;
            foreach($results as $row)
            {
                if ($row['id'] > 0)
                {
                    $name = $row['name'];
                    if ($row['blog_id'] > 0)
                    {
                        $values[] = $name; 
                        $slugs[] = $row['name_clean'];
                    }  
                    else {
                        $available[] = $name; 
                    }
                }
            }
        }
        return $catset;
    }
    
    static public function listCategoryId($catid) 
    {
        $sql =  'select b.id, b.date_published, b.title, b.title_clean from blog b ' .
                    ' join blog_to_category bc on b.id = bc.blog_id and b.enabled = 1 and bc.category_id = :catid' .
                    '  order by b.issue desc, b.id asc';
        $db = Server::db();
        $results = $db->exec($sql, [':catid' => $catid]);
        return $results ? $results : [];    
    }
    static public function url_slug($str)
    {
        #convert case to lower
        $str = strtolower($str);
        #remove special characters
        $str = preg_replace('/[^a-zA-Z0-9]/i', ' ', $str);
        #remove white space characters from both side
        $str = trim($str);
        #remove double or more space repeats between words chunk
        $str = preg_replace('/\s+/', ' ', $str);
        #fill spaces with hyphens
        $str = preg_replace('/\s+/', '-', $str);
        return $str;
    }
    /**
     * Return an unused URL, from title slug and date
     */
    static public function unique_url($blogid, $slug) {
        $sql = 'select count(*) as dupe from blog where title_clean = :tc';
        $isUpdate = !is_null($blogid) && ($blogid > 0);
        $params[':tc'] = $slug;

        if ($isUpdate) {
// exclude self from search, in case of no change?
            $sql .= ' and id <> :bid';
            $params[':bid'] = $blogid;
        }
        $db = Server::db();
        
        $tryCount = 0;

        $date = new \DateTime();
        while ($tryCount < 5) {
            $results = $db->exec($sql,$params);
            $count = empty($results) ? 0 : intval($results[0]['dupe']);
            if ($count === 0) {
                break;
            } else {
                if ($tryCount == 0) {
                    $slug .= '-' . date('Ymd', $date->getTimestamp());
                    $params[':tc'] = $slug;
                } else {
                    $params[':tc'] = $slug . '-' . $tryCount;
                }
            }
            $tryCount += 1;
        }
        return $params[':tc'];
    }

    static public function getEvents($id) {
        $sql = "select e.* from event e where e.blogId = :blogId";
        $db = Server::db();
        $results = $db->exec($sql, ['blogId' => $id]);
        if ($results) {
            return $results;
        } else {
            return [];
        }
    }
  
    // return array, with gallerypaths that reference array of image file names
    static public function imageFiles($article) {
        $search = ["<figure", "</figure", "<figcaption" , "</figcaption"];
        $replace = ["<div", "</div", "<div", "</div"];
        $html = str_replace($search, $replace, $article);
        $old_error_handler = set_error_handler("HTML_ERROR");
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        try {
            
            $dom->loadHTML($html);
            $images = $dom->getElementsByTagName('img');
            $result = [];
            foreach($images as $img) {
                if ($img->hasAttribute('src'))
                {
                    $src = $img->getAttribute('src');
                    $matches = ["/image/gallery/", "/files/"];
                    foreach( $matches as $ix => $dir) {
                        $s = $dir;
                        if (strpos($src,$s) === 0) {
                            $subpath = substr($src, strlen($s));
                            if ($ix === 0) {
                                $pos = strpos($subpath,"/");
                                if ($pos !== false) {
                                    $pos++;
                                    $gallery = substr($subpath,0,$pos);
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
            $errors = libxml_get_errors();

            foreach ($errors as $error)
            {
                $result[] = $error;
            }
            if (empty($result)) {
                return ["OK"];
            }
            return $result;
        }
        catch(\Throwable $e) {
            return $e->getMessage();
        } finally {
            set_error_handler($old_error_handler);
            libxml_clear_errors();
        }
    }
    /**
     * Fill $model  query order information
     * 
     * @param object $model
     * @param string $orderby - key index
     * @return string
     * return sql order fields clause
     */
    static public function viewOrderBy($model, $orderby)
    {
        if (is_null($orderby))
        {
            $orderby = 'date-alt';
        }
        $alt_list = array(
            'date' => 'date',
            'title' => 'title',
            'author' => 'author',
            'update' => 'update'
        );
        $col_arrow = array(
            'date' => '',
            'title' => '',
            'author' => '',
            'update' => ''
        );  
        switch($orderby)
        {
            case 'title':
                $alt_list['title'] = 'title-alt';
                $col_arrow['title'] = '&#8595;';
                $order_field = 'b.title asc';
                break;
            case 'date':
                $alt_list['date'] = 'date-alt';
                $col_arrow['date'] = '&#8595;';
                $order_field = 'b.date_published asc';
                break;
            case 'author':
                $alt_list['author'] = 'author-alt';
                $col_arrow['author'] = '&#8595;';
                $order_field = 'author_name asc, b.date_published desc';
                break;
            case 'update':
                 $alt_list['update'] = 'update-alt';
                $col_arrow['update'] = '&#8595;';
                $order_field = 'b.date_updated asc, b.title asc';              
                break;
             case 'title-alt':
                $col_arrow['title'] = '&#8593;';
                $order_field = 'b.title desc';
                break;   
            case 'author-alt':
                 $col_arrow['author'] = '&#8593;';
                 $order_field = 'author_name desc,  b.date_published desc';
                 break;    
            case 'update-alt':
                 $col_arrow['update'] = '&#8593;';
                 $order_field = 'b.date_updated desc, b.title asc';
                 break;       
            case 'date-alt':
            default:
                $col_arrow['date'] = '&#8593;';
                $order_field = 'b.date_published desc';
                $orderby = 'date-alt';
                break;             
                
        }
        $model->orderalt = $alt_list;
        $model->orderby = $orderby;
        $model->col_arrow = $col_arrow;
        return $order_field;
    }
    static  public function byTitleClean($tc) {
        $result = new Blog();
        return $result->load(['title_clean = ?' , $tc]);
    }    
    static  public function findFirstById($id) {
        $result = new Blog();
        return $result->load('id=' . $id);
    }
    
    public function insertPackage(&$pack, $op) {
        $version = floatval($pack['version']);
        if ($version < 0.2) {
            return false;
        }
        $rec = $pack['blog'];
        $this['title'] = $rec['title'];
        $this['title_clean'] = $rec['title_clean'];
        $this['article'] = $rec['article'];
        $this['date_published'] =  $rec['date_published'];
        $this['date_updated'] =  $rec['date_updated'];
        $this['style'] =  $rec['style'];
        $this['issue'] =  $rec['issue'];

        $db = $this->db;
        if ($op === "save" || $op === "new") {
            $this->save();
            $blogid = $this['id'];
           
        }
        else if ($op === "update")
        {
            $this->update();
            // erase old metainfo
            $blogid = $this['id'];
            $mdel = "delete from blog_meta where blog_id = ?";
            $db->exec([$mdel,$blogid]);
        }
        
        $meta = $pack['meta'];
        
        if (!empty($meta)) {
            $sql = "select id from meta where meta_name = ?";
            foreach( $meta as $md) {
                $meta_name = $md['meta_id'];
                $idresult = $db->exec([$sql, $meta_name]);
                if ($idresult != false) {
                    $mid = $idresult[0]['id'];
                    $db->exec("insert into blog_meta(blog_id, meta_id, content)" 
                            . "values (:bid, :mid, :ct)", 
                            [':bid'=> $blogid, 
                                ':mid'=>$mid, 
                                ':ct' => $md['content']
                                ]);
                }
            }
        }
        $categorys = $pack['categorys'];
        if (!empty($categorys)) {
            foreach($categorys as $slug => $title) {
                // ensure entry in blog_to_category
                $catid = $db->exec('select bc.id where bc.name_clean = :slug', [':slug'=>$slug]);
                if (!empty($catid)) {
                    $db->exec("insert into blog_to_category(blog_id, category_id) values (:b1, :c1)",
                            [":b1" => $blogid, ":c1" => $catid[0]['id'] ]);  
                }
            }
        }
        $images = $pack['images'];
        
        if (!empty($images) ) {
            $f3 = \Base::instance();
            $imageRoot = $f3->get('webDir');
            
            $domain = isset($pack['image_domain']) ? $pack['image_domain'] : null;
            if (empty($domain)) {
                $images = [];
            }
            foreach($images as $gallery => $list) {
                $dir = $imageRoot . $gallery;
                foreach($list as $fname) {
                    $path = $dir . $fname;
                    if (!file_exists($path)) {
                        if (!file_exists($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        $url = $gallery .  $fname;
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
    static public function &package($id) {
        $blog = static::findFirstById($id);
                // get essential data for json, not by keys of this DB
        $f3 = \Base::instance();
        $pack = [];
        $pack['version'] = "0.2";
        $rec = [];
        $rec['title'] = $blog['title'];
        $rec['title_clean'] = $blog['title_clean'];
        $rec['article'] = $blog['article'];
        $rec['date_published'] =  $blog['date_published'];
        $rec['date_updated'] =  $blog['date_updated'];
        $rec['style'] = $blog['style'];
        $rec['issue'] = $blog['issue'];
        
        $file_date = (new \DateTime($rec['date_updated']))->format('d-M-y');
        $file_name = $rec['title_clean'] . "_" . $file_date . ".json";
        
        $pack['packname'] = $file_name;
        $secrets = &$f3->ref('secrets');
        $pack['image_domain'] = $secrets['backups']['image_domain'];
        
        $catset = static::getCategorySet($id);
        $category = [];
        foreach($catset->slugs as $ix => $key) {
            $category[ $key ] = $catset->values[$ix];
        }
        $pack['blog'] = $rec;
        $pack['categorys'] = &$category;
        $pack['events'] = static::getEvents('$id');
        $meta = [];
        $metadata = static::getMetaTagHtml($blog->id,$meta);
        $metapack = [];
        foreach($metadata as $row) {
            $mdata = [];
            $mdata['meta_id'] = $metadata['meta_name'];
            $mdata['attr'] = $metadata['attr'];
            $mdata['data_limit'] = $metadata['data_limit'];
            $mdata['prefixSite'] = $metadata['prefixSite'];
            $mdata['display']  = $metadata['display'];
            $metapack[] = $mdata;
        }
        $pack['meta'] = $metapack;
        $pack['images'] = static::imageFiles($blog['article']);
        
        return $pack;
    }
    
    
    static public function export($id, $path) {
        $pack = static::package($id);
        // get essential data for json, not by keys of this DB
        $pack_json = json_encode($pack);
        file_put_contents($path . $pack['packname'], $pack_json);
        return $fname;
    }
}
