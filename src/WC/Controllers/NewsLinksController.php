<?php

namespace WC\Controllers;

use WC\App;
use ActiveRecord\{Connection, ConnectionManager, Config, Singleton};
use WC\Db\DbQuery;
/**
 * @author michael
 */
class NewsLinksController extends BaseController {
    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;
        
    public function getAllowRole() : string
    {
        return 'User';
    }
    //put your code here
    public function indexAction() {
        $m = $this->getViewModel();
        $db = ConnectionManager::instance()->get_connection('news_db');
        $qry = new DBQuery($db);
        $qry->bindLimit(16,0);
        $qry->order('pub_date desc');
        $qry->whereCondition('flags <> 0');
        $m->data = $qry->queryOA('select L.*, F.provider from rss_link L join rss_feed F on F.id = L.feed_id ');
        
        return $this->render('newslinks', 'index');
    }
}
