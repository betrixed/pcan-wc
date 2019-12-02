<?php

namespace Pcan\DB;
/**
 * Description of Links
 *
 * @author Michael Rynn
 */

use WC\UserSession;
use WC\DB\Server;

class Links extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'links', NULL, 1.0e8); // 100 second
    }
    
    static public function byTitle($title) {
        $result = new Links();
        return $result->load(['title = ?' , $title]);
    }
    // some harder to extract metadata, for size of form fields
    static public function display() {
        return [
            'id' => [
                'type' => 'int',
                'max' => 11,
                'autoinc' => true
            ],
            'url' => [
                'type' => 'varchar',
                'max' => 255,
            ],
            'summary' => [
                'type' => 'text',
                'max' => 65535,
            ],
            'title' =>  [
                'type' => 'varchar',
                'max' => 255,
            ],
             'sitename' =>  [
                'type' => 'varchar',
                'max' => 255,
            ],
            'date_created' => [
                'type' => 'datetime',
                'max' => 0,
            ],
            'enabled' => [
                'type' => 'tinyint',
                'max' => 1,
            ],
            'urltype' => [
                'type' => 'varchar',
                'max' => 12
            ],
            'refid' => [
                'type' => 'int',
                'max' => 11
            ]
        ];
    }
    
    static public function homeLinks() {
        $params = [];
        $db = Server::db();
        // links to recent blog articles
        // recent remote links below front page article
        $sql = <<<EOD
select id, url, title, sitename, summary, urltype, date_created 
  from links
  where (urltype='Remote' or urltype='Front' or urltype='Blog') 
  and enabled = 1
  order by date_created desc
 limit  20
EOD;
        $rows = $db->exec($sql);
        $params['ct'] = count($rows);
        $params['rows'] = $rows;
       //$params['isMobile'] = $this->isMobile();
        return $params;
    }
    
    
    static public function byType($linkType) {
        $params = [];
        $db = Server::db();
        // links to recent blog articles
        // recent remote links below front page article
        $sql = <<<EOD
select id, url, title, sitename, summary, urltype, date_created 
  from links
  where urltype= :utype
  and enabled = 1
  order by date_created desc
 limit  20
EOD;
        $rows = $db->exec($sql, [':utype' => $linkType] );
        $params['ct'] = count($rows);
        $params['rows'] = $rows;
       //$params['isMobile'] = $this->isMobile();
        return $params;
    }
    
    /**
     * Get all  possible UrlTypes as key->value. These are not a Table in the E->R
     */
    static public function getUrlTypes() {
            return [ 
            'Remote' => 'Remote',
            'Blog' => 'Blog',
            'Campaign' => 'Campaign',
            'Front' => 'Front Page',
            'Side' => 'Side Column',
            'Dash' => 'Dash',
            'Panel' => 'Panel',
            'Event' => 'Event'
                ];
    }
    /**
     * 
     * @param type $view    - View to set
     * @param string $orderby  - Handles null case for ordered column name
     * @return string    - table field to order by.
     */
    static public function indexOrderBy($view, $orderby)
    {
        if (is_null($orderby))
        {
            $orderby = 'date-alt';
        }
        $alt_list = array(
            'date' => 'date',
            'title' => 'title',
            'type' => 'type',
            'site' => 'site',
            'enabled' => 'enabled'
        );
        $col_arrow = array(
            'date' => '',
            'title' => '',
            'type' => '',
            'site' => '',
            'enabled' => ''
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
                $order_field = 'b.date_created asc';
                break;
            case 'type':
                $alt_list['type'] = 'type-alt';
                $col_arrow['type'] = '&#8595;';
                $order_field = 'b.urltype asc, b.date_created desc';
                break;
             case 'site':
                $alt_list['site'] = 'site-alt';
                $col_arrow['site'] = '&#8595;';
                $order_field = 'b.sitename asc, b.date_created desc';
                break;     
            case 'enabled':
                $alt_list['enabled'] = 'enabled-alt';
                $col_arrow['enabled'] = '&#8595;';
                $order_field = 'b.enabled desc, b.date_created desc';
                break;     
             case 'title-alt':
                $col_arrow['title'] = '&#8593;';
                $order_field = 'b.title desc';
                break;   
            case 'type-alt':
                 $col_arrow['type'] = '&#8593;';
                 $order_field = 'b.urltype desc,  b.date_created desc';
                 break;   
            case 'site-alt':
                $col_arrow['site'] = '&#8593;';
                $order_field = 'b.sitename desc, b.date_created desc';
                break; 
           case 'enabled-alt':
                $col_arrow['enabled'] = '&#8593;';
                $order_field = 'b.enabled asc, b.date_created asc';
                break;     
            case 'date-alt':
            default:
                $col_arrow['date'] = '&#8593;';
                $order_field = 'b.date_created desc';
                break;             
                
        }
        $view->orderalt = $alt_list;
        $view->orderby = $orderby;
        $view->col_arrow = $col_arrow;
        return $order_field;
    }
    
    static function setBlogURL($blogid, $slug) {
        try {
            $link = (new Links())->load([ 'refid = :rid', ':rid' => $blogid] );
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            UserSession::flash('No link record yet: ' . $err[0] . ' ' . $err[1]);
            $link = false;
            return;
        }
        if ($link !== false) {
            // update the link title and url automagically
            // $link['title'] = $blog['title'];
            $link['url'] = "/article/" . $slug;
            try {
                $link->update(); // will fail if duplicate
            } catch (\PDOException $e) {
                $err = $e->errorInfo;
                UserSession::flash('Link update failed: ' . $err[0] . ' ' . $err[1]);
            }
        }
    }
}
