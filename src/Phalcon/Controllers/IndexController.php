<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;
//use WC\Db\Server;
use WC\Assets;
use WC\Db\DbQuery;
use App\Link\LinksOps;
use WC\Db\Server;
use WC\App;
use WC\FileCache;

//use Phalcon\Cache\Adapter\Stream;
//use Phalcon\Storage\SerializerFactory;

class IndexController extends Controller
{

    use \WC\Mixin\ViewPhalcon;


    /*
      private function getModelCache($storageDir)
      {
      $options = [
      'defaultSerializer' => 'php',
      'lifetime' => 7200,
      'storageDir' => $storageDir
      ];

      $serializerFactory = new SerializerFactory();
      $adapter = new Stream($serializerFactory, $options);
      return $adapter;
      }
     */

    private function events()
    {
        $db = Server::db();
        if ($db->getType() === 'sqlite') {
            $nowfn1 = "datetime( B.fromtime) > datetime('now')";
            $nowfn2 = "datetime( B.totime) > datetime('now')";
        } else {
            $nowfn1 = "B.fromtime > NOW()";
            $nowfn2 = "B.totime > NOW()";
        }
        $qry = <<<EOQ
    SELECT A.id, A.title, B.fromtime as  date1, B.totime as date2,
    R.content as article, A.style, A.title_clean, C.content
    from blog A join blog_revision R on R.blog_id = A.id and R.revision = A.revision
    join event B on A.id = B.blogid and A.enabled = 1
    and (
    ((B.fromtime is NOT NULL) AND ( $nowfn1 ))
    OR ((B.totime  is NOT NULL) AND ( $nowfn2 ))
    )
    join
    (select MC.blog_id, MC.content from blog_meta MC join meta M on MC.meta_id = M.id
    where M.meta_name = 'og:description') C on C.blog_id = A.id
    order by B.fromtime
EOQ;

        return (new DbQuery())->arraySet($qry);
    }

    private function main()
    {

        $qry = <<<EOQ
      select links.id, links.url, links.title,
      links.sitename, links.summary, links.urltype, links.date_created,
      image.name as im_file, gallery.path as im_path, image.description as im_caption
      from links
      left join image on image.id = links.imageid
      left join gallery on gallery.id = image.galleryid

      where (links.urltype='Remote' or links.urltype='Front'
      or links.urltype='Blog' or links.urltype='Event')
      and links.enabled = 1
      order by links.date_created desc
      limit 20
      EOQ;

        return (new DbQuery())->arraySet($qry);
    }
   protected function getMaxHeight(array $images)
   {
       $max = 0;
       foreach($images as $im) {
           $test = $im['height'];
           if ($max < $test) {
               $max = $test;
           }
       }
       return $max;
   }
    public function recentFFB() {
        $qry = <<<EOQ
    select IM.*, G.path from image IM 
    join gallery G on IM.galleryid = G.id
    where G.seriesid = 1
    order by IM.date_upload desc
    LIMIT 4
   EOQ;
         return (new DbQuery())->arraySet($qry);
    }
    public function indexAction()
    {
        $assets = \WC\Assets::instance();
        $assets->add('bootstrap');
        $assets->minify("pcan_home");

        $view = $this->getView();

        /*$cache = FileCache::modelCache();
        $cache_key = 'IndexIndex';
        $m = $cache->get($cache_key, null);
       
        if (is_null($m)) {
         */
            $m = $view->m;
            $m->sides = $this->sides();
            $m->main = $this->main();
            $m->events = $this->events();
            $m->images = $this->recentFFB();
            $m->maxHeight = $this->getMaxHeight($m->images);
            
            $m->title = "PCAN Home";

            $panels = LinksOps::byType('Panel');
            if ($panels['ct'] > 0) {
                $m->topPanels = &$panels['rows'];
            } else {
                $m->topPanels = [];
            }
           /*$cache->set($cache_key, $m);
        } else {
           
        }*/
         $view->m = $m;


        /*

          $view->content = 'front/home.phtml';
          $view->layout = 'front/layout.phtml';
          $view->nav = 'front/nav.phtml'; */
        return $this->render('index', 'index');
    }

    private function sides()
    {
        $qry = <<<EOQ
      select id, url, title, date_created, summary from links
      where urltype='Side' and enabled = 1
      order by date_created desc
      EOQ;
        return (new DbQuery())->arraySet($qry);
    }

    function linksAction()
    {
        $view = $this->getView();
        $req = $this->request;
        $linkType = $req->getQuery('k', 'string', null);
        $m = $view->m;

        $cache_key = 'IndexLinks' . $linkType;
        $cache = new FileCache($this->app->model_cache);
        $m->links = $cache->get($cache_key, null);
        if (empty($m->links)) {
            if (!empty($linkType)) {
                $m->links = LinksOps::byType($linkType);
            } else {
                $m->links = LinksOps::homeLinks();
            }
            $cache->set($cache_key, $m->links);
        }
        if (!empty($linkType)) {
            $select = ['Remote' => 'Web', 'Event' => 'Events', 'Blog' => 'Here'];
            $m->title = $select[$linkType];
        } else {
            $m->title = "All Links";
        }
        $assets = \WC\Assets::instance();
        $assets->add(['bootstrap', 'grid']);
        $assets->minify('home_grid');

        return $this->render('index', 'links');
    }

}
