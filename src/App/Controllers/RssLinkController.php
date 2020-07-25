<?php

declare(strict_types=1);

namespace App\Controllers;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model;
use App\Models\RssLink;
use Masterminds\HTML5;
use App\Link\RssView;

class RssLinkController extends \Phalcon\Mvc\Controller 
{

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;
    /**
     * Index action
     */
    public $text;

    public function getAllowRole() : string
    {
        return 'User';
    }
    public function scanTree($node) {
        while (!empty($node)) {
            if ($node->nodeName === "p") {
                $this->text .= "<p>" . $node->textContent . "</p>";
            } else if ($node->nodeName == "figure" && $node->hasChildNodes()) {
                $this->text .= RssView::grabFigure($node);
            } else if ($node->nodeName == "img") {
                $image_text = RssView::inlineImage($node);
                $this->text .= $image_text;
            } else if ($node->hasChildNodes()) {
                $this->scanTree($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }

    public function dbError($msgs) {
        foreach ($msgs as $message) {
            $this->flash($message->getMessage());
        }
    }
    
    public function resetAction($id) {
        $link = RssLink::findFirstById($id);
        $link->flags = 0;
        $link->extract = "NULL";
        if (!$link->update()) {
            $this->dbError($link->getMessages());
        }
        return $this->viewAction($id);
    }
    public function viewAction($id) {
        $link = RssLink::findFirstById($id);
        $m = $this->getViewModel();
        $m->link = $link;
        $m->feed = $link->getRelated('RssFeed');
        if ((intval($link->flags) & 1) === 0) {
            if (intval($link->flags) === 0) {
                $link->extract = "";
                $content = RssView::pullContent($link->link);
            }
            else {
                $content = $link->extract;
            }
             $rss = new RssView();
            $json = $rss->scanHeaderJSON($content);
            if (!empty($json)) {
                $obj = json_decode($json);
                if (!empty($obj)) {
                    $link->creator = $obj->author->name;
                    if (empty($link->creator)) {
                        $link->creator = "Anon";
                    }
                    $datekey = new \DateTime($obj->datePublished);
                    $link->pubDate = $datekey->format('Y-m-d H:i:s');
                }
                
            }
            $provider = $m->feed->provider;
            if (str_contains($provider, 'News Limited')) {
                     $link->extract =  $rss->scanNewsLimited($content);
            }
            else if (str_contains($provider,'Fairfax Media')) {
                $link->extract = $rss->scanFairfax($content);
            }
            else if  (str_contains($provider,'ABC')) {
                $link->extract = $rss->scanABC($content);
            }
            if (empty($link->extract)) {
                $link->extract = $content;
                $link->flags = 2;
            }
            else {
                $link->flags = 3;
            }
            if (!$link->update()) {
                $this->dbError($link->getMessages());
            }
        }
        return $this->render('rss_link', 'view');
    }

    /**
     * Index action
     */
    public function indexAction() {
        //
    }

    /**
     * Searches for rss_link
     */
    public function searchAction() {
        $numberPage = $this->request->getQuery('page', 'int', 1);
        $parameters = Criteria::fromInput($this->di, '\App\Models\RssLink', $_GET)->getParams();
        $parameters['order'] = "id";

        $paginator = new Model(
                [
            'model' => '\App\Models\RssLink',
            'parameters' => $parameters,
            'limit' => 10,
            'page' => $numberPage,
                ]
        );

        $paginate = $paginator->paginate();

        if (0 === $paginate->getTotalItems()) {
            $this->flash->notice("The search did not find any rss_link");

            $this->dispatcher->forward([
                "controller" => "rss_link",
                "action" => "index"
            ]);

            return;
        }

        $this->view->page = $paginate;
    }

    /**
     * Displays the creation form
     */
    public function newAction() {
        //
    }

    /**
     * Edits a rss_link
     *
     * @param string $id
     */
    public function editAction($id) {
        if (!$this->request->isPost()) {
            $rss_link = RssLink::findFirstByid($id);
            if (!$rss_link) {
                $this->flash->error("rss_link was not found");

                $this->dispatcher->forward([
                    'controller' => "rss_link",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $rss_link->getId();

            $this->tag->setDefault("id", $rss_link->getId());
            $this->tag->setDefault("feed_id", $rss_link->getFeedId());
            $this->tag->setDefault("xml_json", $rss_link->getXmlJson());
            $this->tag->setDefault("extract", $rss_link->getExtract());
            $this->tag->setDefault("extract_title", $rss_link->getExtractTitle());
        }
    }

    /**
     * Creates a new rss_link
     */
    public function createAction() {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "rss_link",
                'action' => 'index'
            ]);

            return;
        }

        $rss_link = new RssLink();
        $rss_link->setfeedId($this->request->getPost("feed_id", "int"));
        $rss_link->setxmlJson($this->request->getPost("xml_json", "int"));
        $rss_link->setextract($this->request->getPost("extract", "int"));
        $rss_link->setextractTitle($this->request->getPost("extract_title", "int"));


        if (!$rss_link->save()) {
            foreach ($rss_link->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "rss_link",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("rss_link was created successfully");

        $this->dispatcher->forward([
            'controller' => "rss_link",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a rss_link edited
     *
     */
    public function saveAction() {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "rss_link",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $rss_link = RssLink::findFirstByid($id);

        if (!$rss_link) {
            $this->flash->error("rss_link does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "rss_link",
                'action' => 'index'
            ]);

            return;
        }

        $rss_link->setfeedId($this->request->getPost("feed_id", "int"));
        $rss_link->setxmlJson($this->request->getPost("xml_json", "int"));
        $rss_link->setextract($this->request->getPost("extract", "int"));
        $rss_link->setextractTitle($this->request->getPost("extract_title", "int"));


        if (!$rss_link->save()) {

            foreach ($rss_link->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "rss_link",
                'action' => 'edit',
                'params' => [$rss_link->getId()]
            ]);

            return;
        }

        $this->flash->success("rss_link was updated successfully");

        $this->dispatcher->forward([
            'controller' => "rss_link",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a rss_link
     *
     * @param string $id
     */
    public function deleteAction($id) {
        $rss_link = RssLink::findFirstByid($id);
        if (!$rss_link) {
            $this->flash->error("rss_link was not found");

            $this->dispatcher->forward([
                'controller' => "rss_link",
                'action' => 'index'
            ]);

            return;
        }

        if (!$rss_link->delete()) {

            foreach ($rss_link->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "rss_link",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("rss_link was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "rss_link",
            'action' => "index"
        ]);
    }

}
