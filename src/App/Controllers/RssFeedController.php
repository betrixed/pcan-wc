<?php

declare(strict_types=1);

namespace App\Controllers;

use WC\Valid;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model;
use App\Models\RssFeed;
use WC\Db\DbQuery;
use WC\UserSession;
use App\Models\RssLink;
use App\Link\RssView;
use Phalcon\Db\Column;

class RssFeedController extends \Phalcon\Mvc\Controller
{

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;

    public $url = "/news/rss_feed/";
    public $newsLimitedExcludes = [
        'Motoring news',
        'Luxury',
        'New cars',
        'Wearables',
        'Gaming',
        'Computers'
    ];

    public function render_action($action): string
    {
        $view = $this->getView();
        $m = $view->m;
        $m->url = $this->url;
        $m->table_name = "&quot;RSS Feed&quot;";
        return $this->render('rss_feed', $action);
    }

    public function getAllowRole(): string
    {
        return 'Admin';
    }

    /**
     * Index action
     */
    public function indexAction()
    {
//
        return $this->render_action('index');
    }

    /**
     * Searches for rss_feed
     */
    function searchAction()
    {
        $m = $this->getViewModel();
        $m->items = [];

        $qry = $this->dbq;
        $sql = "select * from rss_feed";
        $get = $_GET;

        foreach (array_keys($get) as $key) {
            if (!empty($value)) {
                switch ($key) {
                    case "id":
                        $test = Valid::toInt($get, $prop);
                        $criteria = $key . "= ?";
                        break;
                    case "last_read":
                        $test = Valid::toDateTime($get, $prop);
                        $criteria = $key . " < ?";
                        break;
                    default:
                        $test = '%' . Valid::toStr($get, $prop) . '%';
                        $criteria = $key . " like ?";
                        break;
                }
                $qry->bindCondition($criteria, $test);
            }
        }
        $qry->order("last_read");
        $m->items = $qry->queryOA($sql);
        return $this->render_action('search');
    }

    /**
     * Displays the creation form
     * URL https://www.smh.com.au/rss/environment.xml
     * 
     */
    public function newAction()
    {
        $m = $this->getViewModel();
        $m->rec = new RssFeed();
        return $this->render_action('new');
    }

    /**
     * Edits a rss_feed
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {
            $rss_feed = RssFeed::findFirstByid($id);
            if (!$rss_feed) {
                $this->flash->error("rss_feed was not found");

                $this->dispatcher->forward([
                    'controller' => "rss_feed",
                    'action' => 'index'
                ]);

                return;
            }

            $m = $this->getViewModel();
            $m->rec = $rss_feed;
            return $this->render_action('edit');
        }
    }

    public function dbError($msgs)
    {
        foreach ($msgs as $message) {
            $this->flash($message->getMessage());
        }
    }

    /**
     * Creates a new rss_feed
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "rss_feed",
                'action' => 'index'
            ]);

            return;
        }
        $post = $_POST;

        $rss_feed = new RssFeed();
        $rss_feed->url = Valid::toStr($post, 'url');
        $rss_feed->last_read = Valid::now();
        $content = RssView::pullContent($rss_feed->url);
        if (str_starts_with($content[1], "text")) {
            $rss_feed->content = $content[0];
        }

        $rss_feed->nick_name = Valid::toStr($post, "nick_name");
        $rss_feed->provider = Valid::toStr($post, "provider");


        if (!$rss_feed->create()) {
            $this->dbError($rss_feed->getMessages());

            $this->getViewModel()->rec = $rss_feed;

            return $this->render_action("new");
        }

        $this->flash->success("rss_feed was created successfully");
        $this->reroute($this->url . "edit/" . $rss_feed->id);
    }

    /**
     * Saves a rss_feed edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "rss_feed",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $rss_feed = RssFeed::findFirstByid($id);

        if (!$rss_feed) {
            $this->flash("rss_feed does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "rss_feed",
                'action' => 'index'
            ]);

            return;
        }
        $post = $_POST;
        $rss_feed->url = Valid::toStr($post, 'url');

        $rss_feed->nick_name = Valid::toStr($post, "nick_name");
        $rss_feed->provider = Valid::toStr($post, "provider");
        if (empty($rss_feed->content)) {
            $content = RssView::pullContent($rss_feed->url);
            if (str_contains($content[1], "xml")) {
                $rss_feed->content = $content[0];
            }
        }
        if (!$rss_feed->update()) {
            $this->dbError($rss_feed->getMessages());
            $this->getViewModel()->rec = $rss_feed;
            return $this->render_action("edit");
        }

        $this->flash("rss_feed was updated successfully");

        $this->reroute($this->url . "edit/" . $rss_feed->id);
    }

    public function processFairfax($id, $chan): array
    {
        $itemlist = $chan->getElementsByTagName('item');
        $items = [];
        foreach ($itemlist as $item) {
            $node = $item->firstChild;
            while (!empty($node)) {
                switch ($node->nodeName) {
                    case 'guid':
                        $guid = strval($node->nodeValue);
                        break;
                }
                $node = $node->nextSibling;
            }

            $link = RssLink::findFirst(
                            ['conditions' => "feed_id = :id: and guid = :gid:",
                                'bind' => ['id' => $id, 'gid' => $guid],
                                'bindTypes' => ['id' => Column::BIND_PARAM_INT, 'gid' => Column::BIND_PARAM_STR]
            ]);

            if (empty($link)) {
                $link = new RssLink();
                $link->guid = $guid;
                $link->feed_id = $id;


                $node = $item->firstChild;
                while (!empty($node)) {
                    switch ($node->nodeName) {
                        case 'title':
                            $link->title = $node->nodeValue;
                            break;
                        case 'link':
                            $link->link = $node->nodeValue;
                            $content = RssView::pullContent($link->link);
                            if (str_starts_with($content[1], "text")) {
                                $link->extract = $content[0];
                            }
                            break;
                        case 'dc:creator':
                            $link->creator = $node->nodeValue;
                            break;
                        case 'description':

                            $link->description = strip_tags($node->nodeValue);
                            break;
                        case 'section':
                            $link->section = $node->nodeValue;
                            break;
                        case 'pubDate':
                            $datekey = new \DateTime($node->nodeValue);
                            break;
                    }
                    $node = $node->nextSibling;
                }
                if (empty($link->creator)) {
                    $link->creator = "Anon.";
                }
                $link->pub_date = $datekey->format('Y-m-d H:i:s');
                if (!$link->create()) {
                    $this->dbError($link->getMessages());
                    break;
                }
            }
            $items[] = $link;
        }

        return $items;
    }

    // Get all the tags and attributes
    public function extractHtml($node)
    {
        return $node->textContent;
    }

    public function processSkepticalScience($feed, $chan): array
    {
        $id = $feed->id;
        $itemlist = $chan->getElementsByTagName('item');
        $items = [];
        foreach ($itemlist as $item) {
            //  description (a lot of HTML)
            //  link
            // guid same as link id
            // pubDate
            $node = $item->firstChild;
            $values = [];
            while (!empty($node)) {
                if ($node->nodeType === XML_ELEMENT_NODE) {
                    switch ($node->nodeName) {
                        case 'pubDate':
                            $values['pubDate'] = new \DateTime($node->nodeValue);
                            break;
                        case 'description':
                        case 'guid':
                        case 'title':
                        case 'link':
                        DEFAULT:
                            $values[$node->nodeName] = $node->nodeValue;
                            break;
                    }
                }
                $node = $node->nextSibling;
            }
            
            if (count($values) === 5) {
                $guid = $values['guid'];
                $link = RssLink::findFirst(
                            ['conditions' => "feed_id = :id: and guid = :gid:",
                                'bind' => ['id' => $id, 'gid' => $guid],
                                'bindTypes' => ['id' => Column::BIND_PARAM_INT, 'gid' => Column::BIND_PARAM_STR]
                ]);
                
                if (empty($link)) {
                    $link = new RssLink();
                    $link->feed_id = $id;
                    $link->description = $values['description'];
                    $link->title = $values['title'];
                    $link->link = $values['link'];
                    $link->guid = $guid;
                    $link->pub_date = $values['pubDate']->format('Y-m-d H:i:s');
                    $link->create();
                }
                $items[] = $link;
            }
        }
        return $items;
    }

    public function processGuardian($feed, $chan): array
    {
        $id = $feed->id;
        $itemlist = $chan->getElementsByTagName('item');
        $items = [];
        foreach ($itemlist as $item) {
            $node = $item->firstChild;
            while (!empty($node)) {
                switch ($node->nodeName) {
                    case 'guid':
                        $guid = strval($node->nodeValue);
                        break;
                }
                $node = $node->nextSibling;
            }

            $link = RssLink::findFirst(
                            ['conditions' => "feed_id = :id: and guid = :gid:",
                                'bind' => ['id' => $id, 'gid' => $guid],
                                'bindTypes' => ['id' => Column::BIND_PARAM_INT, 'gid' => Column::BIND_PARAM_STR]
            ]);

            if (empty($link)) {
                $link = new RssLink();
                $link->guid = $guid;
                $link->feed_id = $id;


                $node = $item->firstChild;
                while (!empty($node)) {
                    switch ($node->nodeName) {
                        case 'title':
                            $link->title = $node->nodeValue;
                            break;
                        case 'link':
                            $link->link = $node->nodeValue;
                            $content = RssView::pullContent($link->link);
                            if (str_starts_with($content[1], "text")) {
                                $link->extract = $content[0];
                            }
                            break;
                        case 'dc:creator':
                            $link->creator = $node->nodeValue;
                            break;
                        case 'description':

                            $link->description = strip_tags($node->nodeValue);
                            break;
                        case 'section':
                            $link->section = $node->nodeValue;
                            break;
                        case 'pubDate':
                            $datekey = new \DateTime($node->nodeValue);
                            break;
                    }
                    $node = $node->nextSibling;
                }
                if (empty($link->creator)) {
                    $link->creator = "Anon.";
                }
                $link->pub_date = $datekey->format('Y-m-d H:i:s');
                if (!$link->create()) {
                    $this->dbError($link->getMessages());
                    break;
                }
            }
            $items[] = $link;
        }

        return $items;
    }

    public function processABC($feed, $chan): array
    {
        $id = $feed->id;
        //https://www.abc.net.au/news/
        $itemlist = $chan->getElementsByTagName('item');
        $items = [];
        foreach ($itemlist as $item) {
            $node = $item->firstChild;
            while (!empty($node)) {
                switch ($node->nodeName) {
                    case 'guid':
                        $guid = (string) $node->nodeValue; // whole URL is too big
                        $s_guid = explode('/', $guid);
                        // go for the numeric parts only. last 3 bits are date/slug/number
                        $sct = count($s_guid);
                        if ($sct > 2) {
                            $guid = $s_guid[$sct - 3] . '/' . $s_guid[$sct - 1];
                        }
                        break;
                }
                $node = $node->nextSibling;
            }

            $link = RssLink::findFirst(
                            ['conditions' => "feed_id = :id: and guid = :gid:",
                                'bind' => ['id' => $id, 'gid' => $guid],
                                'bindTypes' => ['id' => Column::BIND_PARAM_INT, 'gid' => Column::BIND_PARAM_STR]
            ]);

            if (empty($link)) {
                $link = new RssLink();
                $link->guid = $guid;
                $link->feed_id = $id;


                $node = $item->firstChild;
                $cat_list = [];
                while (!empty($node)) {
                    switch ($node->nodeName) {
                        case 'title':
                            $link->title = $node->nodeValue;
                            break;
                        case 'category':
                            $cat_list[] = $node->nodeValue;
                            break;
                        case 'link':
                            $link->link = $node->nodeValue;
                            $content = RssView::pullContent($link->link);
                            if (str_starts_with($content[1], "text")) {
                                $link->extract = $content[0];
                            }
                            break;
                        case 'dc:creator':
                            $link->creator = $node->nodeValue;
                            break;
                        case 'description':
                            $link->description = $node->nodeValue;
                            break;
                        case 'section':
                            $link->section = $node->nodeValue;
                            break;
                        case 'pubDate':
                            $datekey = new \DateTime($node->nodeValue);
                            break;
                    }
                    $node = $node->nextSibling;
                }
                if (empty($link->creator)) {
                    $link->creator = "Anon.";
                }
                $link->category = json_encode($cat_list);

                $link->pub_date = $datekey->format('Y-m-d H:i:s');
                if (!$link->create()) {
                    $this->dbError($link->getMessages());
                    break;
                }
            }
            $items[] = $link;
        }

        return $items;
    }

    /**
     * News articles have last part of URL as unique string
     * @param type $url
     * @return string
     */
    public function extractSlug($url): string
    {
        $ix = strpos($url, '?');
        if ($ix > 0) {
            $url = substr($url, 0, $ix);
        }
        $ix = strrpos($url, '/');
        if ($ix > 0) {
            $url = substr($url, $ix + 1);
        }
        return $url;
    }

    public function processNewsLimited($feed, $chan): array
    {
        $id = $feed->id;
        $pub = $chan->getElementsByTagName('pubDate');
        if (!empty($pub)) {
            $datekey = new \DateTime($pub[0]->nodeValue);
        }
        $itemlist = $chan->getElementsByTagName('item');
        $items = [];
        foreach ($itemlist as $item) {
            $node = $item->firstChild;
            while (!empty($node)) {
                switch ($node->nodeName) {
                    case 'link':
                        $guid = $this->extractSlug($node->nodeValue);
                        break;
                }
                $node = $node->nextSibling;
            }

            $link = RssLink::findFirst(
                            ['conditions' => "feed_id = :id: and guid = :gid:",
                                'bind' => ['id' => $id, 'gid' => $guid],
                                'bindTypes' => ['id' => Column::BIND_PARAM_INT, 'gid' => Column::BIND_PARAM_STR]
            ]);
            if (!empty($link)) {
                if (in_array($link->section, $this->newsLimitedExcludes)) {
                    $link->delete();
                } else {
                    $items[] = $link;
                }
                continue;
            }

            $link = new RssLink();
            $link->guid = $guid;
            $link->feed_id = $id;


            $node = $item->firstChild;
            while (!empty($node)) {
                switch ($node->nodeName) {
                    case 'title':
                        $link->title = $node->nodeValue;
                        break;
                    case 'link':
                        $link->link = $node->nodeValue;
                        break;
                    case 'dc:creator':
                        $link->creator = $node->nodeValue;
                        break;
                    case 'description':
                        $link->description = $node->nodeValue;
                        break;
                    case 'section':
                        $link->section = $node->nodeValue;
                        break;
                }
                $node = $node->nextSibling;
            }
            if (empty($link->creator)) {
                $link->creator = "Anon.";
            }
            $link->pub_date = $datekey->format('Y-m-d H:i:s');

            if (!in_array($link->section, $this->newsLimitedExcludes)) {
                $items[] = $link;
                $content = RssView::pullContent($link->link);
                if (str_starts_with($content[1], "text")) {
                    $link->extract = $content[0];
                }
                if (!$link->create()) {
                    $this->dbError($link->getMessages());
                    break;
                }
            }
        }

        return $items;
    }

    /*
      <item>
      <dc:creator>Peter Hannam</dc:creator>
      <description>It turns out that even the grass is greener during a drought with solar panels, or so one farmer near Dubbo found.</description>
      <guid isPermaLink="false">p555ba</guid>
      <link>https://www.smh.com.au/environment/climate-change/the-surprising-way-renewables-can-help-farmers-cope-20200623-p555ba.html?ref=rss&amp;utm_medium=rss&amp;utm_source=rss_environment</link>
      <pubDate>Wed, 24 Jun 2020 12:32:08 +1000</pubDate>
      <title>The surprising way renewables can help farmers cope</title>
      </item>
     */

    public function processAction($id)
    {
// read items, and check for new ones.
        $rss_feed = RssFeed::findFirstByid($id);
        if (empty($rss_feed)) {
            $this->flash->error("rss_feed was not found");

            $this->dispatcher->forward([
                'controller' => "rss_feed",
                'action' => 'index'
            ]);
            return;
        }
        $last_read = new \DateTime($rss_feed->last_read);
        $now = new \DateTime();
        $diff = $now->diff($last_read);
        $getnew =  ($diff->y > 0 || $diff->m > 0 || $diff->d > 0 || $diff->h > 23);
        if ($getnew) {
            $content = RssView::pullContent($rss_feed->url);
            if (str_contains($content[1], "xml")) {
                $rss_feed->content = $content[0];
            }
        }
        $rss = new \DOMDocument();
        $rss->loadXML($rss_feed->content);
        $items = [];
        $chanlist = $rss->getElementsByTagName('channel');
        $provider = $rss_feed->provider;

        $fnp = "process" . $provider;

        foreach ($chanlist as $chan) {
            $items = $this->$fnp($rss_feed, $chan);
        }


        $m = $this->getViewModel();
        $m->items = $items;
        $m->feed = $rss_feed;
        return $this->render_action("process");
    }

    /**
     * Deletes a rss_feed
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $rss_feed = RssFeed::findFirstByid($id);
        if (!$rss_feed) {
            $this->flash->error("rss_feed was not found");

            $this->dispatcher->forward([
                'controller' => "rss_feed",
                'action' => 'index'
            ]);

            return;
        }

        if (!$rss_feed->delete()) {

            foreach ($rss_feed->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "rss_feed",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("rss_feed was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "rss_feed",
            'action' => "index"
        ]);
    }

}
