<?php

namespace WC\Link;


/**
 * Description of Links
 *
 * @author Michael Rynn
 */
use WC\UserSession;
use WC\Db\Server;
use WC\Db\DbQuery;
use WC\Models\Links;

trait LinksOps  {


    // some harder to extract metadata, for size of form fields
    static public function links_display() {
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
            'title' => [
                'type' => 'varchar',
                'max' => 255,
            ],
            'sitename' => [
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

    public function links_deleteId($id) {
        $db = $this->db;
        $db->execute('delete from links where id = ?', [$id]);
    }

    static  private function links_join() : string {
$sql = <<<EOD
select A.id, A.url, A.title,
 A.sitename, A.summary, A.urltype, A.date_created,
 I.name as im_file, G.path as im_path, 
    I.description as im_caption
 from links A
 left join image I on I.id = A.imageid
 left join gallery G on G.id = I.galleryid
 where A.enabled = 1 and
EOD;
    return $sql;
    }
    /**
     * 
       Recent list of remote links below front page article
     * @return array ; record set;
     */
    public function homeLinks( ) : array {
        // 
        $sql = self::links_join();
        $sql .= <<<EOD
  (A.urltype='Remote' 
  or A.urltype='Front' 
  or A.urltype='Blog') 
  order by A.date_created desc
  limit  20
EOD;
        $qry = new DbQuery($this->db);
        $params['rows'] = $qry->arraySet($sql);
        $params['ct'] = count($params['rows']);

        return $params;
    }
    /**
     * 
       Recent blog links below front page article
     * @return array ; record set;
     */
    function links_byType($linkType) : array {
        $sql = self::links_join();
        $sql .= <<<EOD
  A.urltype= :utype
  order by A.date_created desc
  limit 20
EOD;
        $qry = new DbQuery($this->db);
        $rows = $qry->arraySet($sql, ['utype' => $linkType]);
        $params['ct'] = count($rows);
        $params['rows'] = $rows;

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
     * @param type $model    - model object to set
     * @param string $orderby  - Handles null case for ordered column name
     * @return string    - table field to order by.
     */
    static public function indexOrderBy($model, $orderby) {
        if (is_null($orderby)) {
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
        switch ($orderby) {
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
                $orderby = 'date-alt';
                break;
        }
        $model->orderalt = $alt_list;
        $model->orderby = $orderby;
        $model->col_arrow = $col_arrow;
        return $order_field;
    }

    function links_setBlogURL(int $blogid, string $slug) {
        try {
            $link = Links::findFirst(['refid = ?0', 'bind' => [$blogid]]);
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash('No link record yet: ' . $err[0] . ' ' . $err[1]);
            $link = null;
            return;
        }
        if (!empty($link)) {
            // update the link title and url automagically
            // $link['title'] = $blog['title'];
            $link->url = "/article/" . $slug;
            try {
                $link->update(); // will fail if duplicate
            } catch (\PDOException $e) {
                $err = $e->errorInfo;
               $this->flash('Link update failed: ' . $err[0] . ' ' . $err[1]);
            }
        }
    }

    /**
     * Set enabled field on record
     * @param integer $id
     * @param integer $op
     */
    static public function links_enableId($id, $op) {
        $db = $this->db;
        $db->execute("update links set enabled = ? where id = ?", [$op, $id]);
    }

}
