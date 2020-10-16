<?php

namespace App\Controllers;

use App\Models\Blog;
use App\Models\Event;
use App\Models\Register;
use WC\Db\DbQuery;
use WC\App;
use WC\WConfig;
use WC\Valid;
use WC\UserSession;
use WC\SwiftMail;
//! Front-end processorg
use \Phalcon\Db\Column;
use App\Html2Text\Html2Text;

class RegisterController extends BaseController {

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Captcha;
    use \App\Link\RevisionOp;
    
    const formid = "regevt";

    /** Get the related blog and revision data
     */
    private function getEventBlog($eid, $rid) {
        $qry = $this->dbq;  
       
        
        $sql = <<<EOD
select e.fromTime, e.toTime, e.enabled, e.id as eventid, e.reg_detail,
  b.* , r.content as article from event e 
  join blog b on b.id = e.blogid
  join blog_revision r on r.blog_id = b.id and r.revision = e.revisionid'
EOD;     
         
        $qry->whereCondition('e.id = ?', (int) $eid);
        $result = $qry->queryAO($sql);
        if (!empty($result)) {
            return $result[0];
        } else
            return null;
    }

    // Return first current event associated with $slug
    private function getSlugId(string $slug) {
        $db = $this->dbq;
        $sql = <<<EOD
select e.*
 from event e
 where e.slug = :slug
 and NOW() < e.fromTime
 and e.enabled=1 
     order by e.fromTime
     LIMIT 1 OFFSET 0
EOD;
        $result = $db->arraySet($sql,
                ['slug' => $slug],
                ['slug' => Column::BIND_PARAM_STR]);
        if (!empty($result)) {
            return $result[0];
        } else
            return null;
    }

    function getTotal(int $eventid) : int {
        $qry = $this->dbq;
        $sql = "select sum(people+1) as tote from register where eventid = :evt";
        $qry->bindParam('evt', $eventid);
        $result = $qry->queryAA($sql);
        if (!empty($result)) {
            return $result[0]['tote'];
        }
        else {
            return 0;
        }
    }
    function newRegAction($eventId) {

        if ($this->need_ssl()) {
            return $this->secure_connect();
        }


        $m = $this->formModel();
        $event = null;
        if (! is_numeric($eventId)) {
            $event_set  = Event::find([ 
                'conditions' => 'slug = :slug: and NOW() < fromtime and enabled = 1',
                'bind' => [ 'slug' => $eventId],
                'order' => 'fromtime',
                'limit' => 1
                ]);
            $event = $event_set->getFirst();

        } else {
            $event = Event::findFirstById($eventId);
        }
        
        if (empty($event)) {
            return $this->noAccess();
        }
        
        $m->event = $event;
        
        $m->eblog = $this->getBlogAndRevision($event->blogid, $event->revisionid);
        
        $m->register = new Register();
        $m->register->people = 0;
        $m->totalCount = $this->getTotal($event->id);
        return $this->render('events', 'register');
    }

    private function error($msg) {
        $this->flash($msg);
    }

    private function formModel() : object {
        $m = $this->getViewModel();
        $this->captchaView($m);
        $this->xcheckView($m);
        $m->formid = self::formid;
        $m->eblog = null;
        return $m;
    }
    function editAction($code, $regid) {
        if ($this->need_ssl()) {
            return $this->secure_connect();
        }

        $m = $this->formModel();

        if (!empty($regid)) {
            $rec = Register::findFirstById($regid);
            // Get the record 
            if (empty($rec)) {
                $rec = new Register();
                $this->flash("Register link not found");
            }
            $eventId = $rec->eventid;
            if ($code !== $rec->linkcode) {
                $m->register = new Register();
                $m->register->people = 0;
            } else {
               
                $m->register = $rec;
            }
            $event = Event::findFirstById($eventId);
            if (empty($event)) {
                return $this->noAccess();
            }
            $m->event = $event;
            $m->eblog = $this->getBlogAndRevision($event->blogid, $event->revisionid);
            
        }
        $m->editUrl =  $this->urlPrefix() . '/reglink/' . $rec->linkcode . '/' . $rec->id;
        $m->totalCount = $this->getTotal($event->id);
        return $this->render('events', 'register');
    }

    public function urlPrefix(): string {
        return $_SERVER['REQUEST_SCHEME']
                . '://' . $_SERVER['HTTP_HOST'];
    }

    // return full edit link url
    private function sendLinkEmail($rec): string {
        $app = $this->app;
        $m = $this->getViewModel();
        $editUrl = $this->urlPrefix() . '/reglink/' . $rec->linkcode . '/' . $rec->id;
        if (!empty($rec->email)) {
            $name = $rec->fname . ' ' . $rec->lname;
            // model for email template
            $model = new WConfig();
            $model->link = $editUrl;
            $model->userName = $name;
            $model->domain = $app->organization;
            $model->detail = $rec->reg_detail ?? null;

            $params['m'] = $model;
            $params['app'] = $app;

            $htmlMsg = static::simpleView('events/signup_html', $params);
            $textMsg = (new Html2Text($htmlMsg))->getText();

            $mailer = new SwiftMail($this->app);
            $msg = [
                "subject" => 'Event registration for ' . $m->eblog->title,
                "text" => $textMsg,
                "html" => $htmlMsg,
                "to" => [
                    "email" => $rec->email,
                    "name" => $name
                ]
            ];
            $isValid = $mailer->send($msg);
            if ($isValid['success']) {
                $this->flash('Email sent');
            } else {
                $this->error($isValid['errors']);
            }
        }
        return $editUrl;
    }

    function renderResend($rec) {
        $m = $this->getViewModel();
        $m->register = $rec;

        if ($this->request->isAjax()) {
            $this->noLayouts();
            return $this->render('partials', 'events/resend');
        } else {
            return $this->render('events', 'resend');
        }
    }

    function regPostAction() {
        $m = $this->getViewModel();
        $m->formid = self::formid;
        $post = $_POST;

        $eventid = Valid::toInt($post, 'eventid');
        $regid = Valid::toInt($post, 'id');
        

        $delete = Valid::toStr($post, 'delete');
        $resend = Valid::toStr($post, 'resend');
        $worked = true;
        $event = Event::findFirstById($eventid);
        $m->eblog = $this->getBlogAndRevision($event->blog_id, $event->revisionid);
        $m->event = $event;
        if (!empty($resend)) {
            $rec = Register::findFirstById($regid);
            $m->editUrl = $this->sendLinkEmail($rec);
            return $this->renderResend($rec);
        }
        if (!empty($regid)) {
            // Get the record 
            $rec = Register::findFirstById($regid);
            if (!empty($rec) && !empty($delete)) {
                // this record will be deleted
                $rec->delete();
                $rec = new Register();
                $rec->eventid = $eventid;
                $this->flash('Previous registration deleted');
                $worked = false;
            }
        } else {
            $rec = new Register();
            $rec->eventid = $eventid;
            $rec->created_at = Valid::now();
        }

        if ($worked) {
            $lname = Valid::toStr($post, 'lname');
            $fname = Valid::toStr($post, 'fname');
            $email = Valid::toEmail($post, 'email');
            $people = Valid::toInt($post, 'people');
            $phone = Valid::toPhone($post, 'phone');
            $notkeep = Valid::toBool($post, 'notkeep');
            
            if (empty($fname) || empty($lname) || empty($email)) {
                $this->error('Name and Email required');
                $worked = false;
            } else {
                $rec->fname = $fname;
                $rec->lname = $lname;

                if ($email !== $rec->email) {
                    $rec->email = $email;
                    $rec->linkcode = md5(strtolower($rec->email) . $rec->eventid . strtolower($rec->fname) . strtolower($rec->lname));
                }
                $rec->phone = $phone;
                $rec->people = $people;
                $rec->notkeep = $notkeep;
                
                try {
                    if (empty($regid)) {
                        $op = 'created';
                        $other = Register::findFirst("eventid = $eventid and email = '$email'");
                        if (!empty($other)) {
                            return $this->renderResend($other);
                        }
                        $rec->create();
                    } else {
                        $op = 'updated';
                        $rec->update();
                    }
                } catch (\Exception $ex) {
                    $this->error('Failed to save register for event');
                    $worked = false;
                }
                if ($worked) {
                    $this->flash('Your registration was ' . $op);
                }
            }
        }

        if ($worked) {
            $m->editUrl = $this->sendLinkEmail($rec);
        } else {
            $m->editUrl = "";
        }
        $m->register = $rec;
        $m->totalCount = $this->getTotal($event->id);
        if ($this->request->isAjax()) {
            $this->noLayouts();
            return $this->render('partials', 'events/regform');
        } else {
            return $this->render('events', 'register');
        }
    }

}
