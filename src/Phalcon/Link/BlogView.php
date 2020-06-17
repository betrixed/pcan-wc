<?php

namespace App\Link;

use WC\Db\Server;
use WC\Db\DbQuery;
use Phalcon\Db\Column;
use App\Models\Blog;
use App\Models\BlogRevision;
use WC\Valid;
use WC\App;
/**
 *
 * @author Michael Rynn
 */


class BlogView  {
    static public function hasPrefix($s, $p) {
        $p1 = strlen($p);
        return ($p1 <= strlen($s) && $p1 > 0 && substr($s, 0, $p1) === $p);
    }

    // return highest revision number plus 1
   static function  newRevision(Blog $blog) : int {
        $q = new DbQuery();
        $result = $q->arrayColumn('SELECT MAX(revision) as max_revision from blog_revision where blog_id = :bid',
                                                ['bid' => $blog->id], ['bid'=>Column::BIND_PARAM_INT]);
        if (!empty($result)) {
            return intval($result[0]) + 1;
        }
        return 1;
   }
    static function pageFromRequest($m) {
        $request = $_GET;
        $m->args = $_SERVER['QUERY_STRING'];
        $m->numberPage = Valid::toInt($request, 'page', 1);
        $m->catId = Valid::toInt($request, 'catId', 0);
        $m->orderby = Valid::toStr($request, 'orderby', null);
        $m->order_field = BlogView::viewOrderBy($m, $m->orderby);

        $m->grabsize = 12;
        $m->start = ($m->numberPage - 1) * $m->grabsize;

        $sql = <<<EOS
SELECT  B.*, R.content as article, R.date_saved, M.content as author_name,
  count(*) over() as full_count from blog B
  JOIN blog_revision R on R.blog_id = B.id and R.revision = B.revision
  JOIN blog_meta M on M.blog_id = B.id JOIN meta T on T.id = M.meta_id and T.meta_name = 'author'
EOS;
        $binders = [];
        if ($m->catId > 0) {
            $sql .= " INNER JOIN blog_to_category BC on BC.blog_id = B.id and BC.category_id = :catId";
            $binders['catId'] = $m->catId;
        }
        $sql .= " ORDER BY  " . $m->order_field
                . " LIMIT " . $m->grabsize . " OFFSET " . $m->start;
        $qry = new DbQuery();

        $results = $qry->arraySet($sql, $binders);

        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;

        $m->page = new PageInfo($m->numberPage, $m->grabsize, $results, $maxrows);
        $items = $qry->arraySet("select id, name from blog_category");
        $selcat = ['0' => 'Any'];
        if (!empty($items)) {
            foreach($items as $row) {
                $selcat[$row['id']] = $row['name'];
            }
        }
        $m->catItems = $selcat;
        $m->isEditor = true;

    }
        static public  function getMetaTags($id) {
// setup metatag info
        $sql = "select m.id, m.meta_name,"
                . "m.template, m.data_limit, b.blog_id, b.content"
                . " from meta m"
                . " left join blog_meta b on b.meta_id = m.id"
                . " and b.blog_id = :blogId";
// form with m_attr_value as labels, content as edit text.
        $db =  new DbQuery();
        $results = $db->arraySet($sql, ['blogId' => $id]);
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
     
     static public function linkedRevision($blog) {
         $rev = BlogRevision::findFirst([
                    'conditions' => 'blog_id = :bid: and revision = :rev:',
                    'bind' => [ 'bid' => $blog->id, 'rev' => $blog->revision]
         ]);
         return $rev;
     }
     
    static public function getMetaTagHtml($id, &$meta) {
        // setup metatag info
        $sql = <<<EOD
select m.*, b.content
    from meta m
    join blog_meta b on b.meta_id = m.id
    and b.blog_id = :id
    order by meta_name
EOD;
        $db = new DbQuery();
        $results = $db->arraySet($sql, ['id' => $id]);
        //$scheme = $server['REQUEST_SCHEME'];
        $sitePrefix = 'http' . '://' . $_SERVER['HTTP_HOST'];
    
        if ($results && count($results) > 0) {
            if (is_array($meta)) {
                $meta_tags = [];
                foreach ($results as $row) {
                    $content = str_replace("'", "&apos;", $row['content']);
                    // replace ' with &apos; 
                    
                    if ($row['prefix_site'] && !static::hasPrefix($content, "http")) {
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
                . " and b.blog_id = :blogid order by c.name";
        $db = new DbQuery();
        $results = $db->arraySet($sql, ['blogid' => $id],['blogid' => Column::BIND_PARAM_INT]);
        $values = [];
        $slugs = [];
        $available = [];
        if (!empty($results)) {
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
         $catset = new \stdClass();
         $catset->cat_blogid = $id;
        $catset->catlist = $results;
        $catset->values = $values;
        $catset->available = $available;
        $catset->slugs = $slugs;
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
        $params['tc'] = $slug;
        $bind['tc'] = Column::BIND_PARAM_STR;
        if ($isUpdate) {
// exclude self from search, in case of no change?
            $sql .= ' and id <> :bid';
            $params['bid'] = $blogid;
             $bind['bid'] = Column::BIND_PARAM_INT;
        }
        $db = new DbQuery();
        
        $tryCount = 0;

        $date = new \DateTime();
        while ($tryCount < 5) {
            $results = $db->arraySet($sql,$params,$bind);
            $count = empty($results) ? 0 : intval($results[0]['dupe']);
            if ($count === 0) {
                break;
            } else {
                if ($tryCount == 0) {
                    $slug .= '-' . date('Ymd', $date->getTimestamp());
                    $params['tc'] = $slug;
                } else {
                    $params['tc'] = $slug . '-' . $tryCount;
                }
            }
            $tryCount += 1;
        }
        return $params['tc'];
    }

    static public function getEvents($id) {
        $sql = "select e.* from event e where e.blogId = :blogId";
        $db = new DbQuery();
        $results = $db->arraySet($sql, ['blogId' => $id]);
        if ($results) {
            return $results;
        } else {
            return [];
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
            'slug' => 'slug'
        );
        $col_arrow = array(
            'date' => '',
            'title' => '',
            'author' => '',
            'slug' => ''
        );  
        switch($orderby)
        {
                case 'slug':
                $alt_list['slug'] = 'slug-alt';
                $col_arrow['slug'] = '&#8595;';
                $order_field = 'B.title_clean asc';
                break;
            case 'title':
                $alt_list['title'] = 'title-alt';
                $col_arrow['title'] = '&#8595;';
                $order_field = 'B.title asc';
                break;
            case 'date':
                $alt_list['date'] = 'date-alt';
                $col_arrow['date'] = '&#8595;';
                $order_field = 'R.date_saved asc';
                break;
            case 'author':
                $alt_list['author'] = 'author-alt';
                $col_arrow['author'] = '&#8595;';
                $order_field = 'author_name asc, R.date_saved desc';
                break;
             case 'title-alt':
                $col_arrow['title'] = '&#8593;';
                $order_field = 'B.title desc';
                break;   
        case 'slug-alt':
                $col_arrow['slug'] = '&#8593;';
                $order_field = 'B.title_clean desc';
                break;
            case 'author-alt':
                 $col_arrow['author'] = '&#8593;';
                 $order_field = 'author_name desc, R.date_saved desc';
                 break;    
            case 'date-alt':
            default:
                $col_arrow['date'] = '&#8593;';
                $order_field = 'R.date_saved desc';
                $orderby = 'date-alt';
                break;             
                
        }
        $model->orderalt = $alt_list;
        $model->orderby = $orderby;
        $model->col_arrow = $col_arrow;
        return $order_field;
    }
   
}
