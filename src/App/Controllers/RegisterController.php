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

class RegisterController extends \Phalcon\Mvc\Controller {

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Captcha;

    // Display Event blog with new register info


    private function getEventBlog($eid) {
        $db = $this->dbq;

        $sql = <<<EOD
select e.fromTime, e.toTime, e.enabled, e.id as eventid, e.reg_detail,
 b.* , r.content as article from event e 
 join blog b on b.id = e.blogid
 join blog_revision r on r.blog_id = b.id and r.revision = b.revision
 where e.id = :eid
EOD;
        $result = $db->arraySet($sql,
                ['eid' => $eid],
                ['eid' => Column::BIND_PARAM_INT]);
        if (!empty($result)) {
            return $result[0];
        } else
            return null;
    }

    private function getSlugId($slug) {
        $db = $this->dbq;
        $sql = <<<EOD
select e.fromTime, e.toTime, e.enabled, e.id as eventid, 
 b.*, r.content as article, r.date_saved as date_updated
 from event e 
 join blog b on b.id = e.blogid
join blog_revision r on r.blog_id = b.id and r.revision = b.revision
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

    function newRegAction($eventId) {

        if ($this->need_ssl()) {
            return $this->secure_connect();
        }


        $m = $this->getViewModel();

        /* $view->content = 'events/register.phtml';
          $view->assets(['bootstrap', 'register-js']);
         */

        $this->captchaView($m);
        $this->xcheckView($m);

        if (is_numeric($eventId)) {
            $result = $this->getEventBlog($eventId);
        } else {
            $result = $this->getSlugId($eventId);
        }
        $m->eblog = $result;

        $m->register = new Register();
        $m->register->people = 0;
        return $this->render('events', 'register');
    }

    private function error($msg) {
        $this->flash($msg);
    }

    function editAction($code, $regid) {
        if ($this->need_ssl()) {
            return $this->secure_connect();
        }

        $view = $this->getView();
        $view->content = 'events/register.phtml';


        $m = $view->m;

        $this->captchaView($m);
        $this->xcheckView($m);

        $m->eblog = null;
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
                $m->eblog = $this->getEventBlog($eventId);
                $m->register = $rec;
            }
        }
        $m->editUrl = '/reglink/' . $rec->linkcode . '/' . $rec->id;
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
                "subject" => 'Event registration for ' . $m->eblog['title'],
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
        $view = $this->getView();
        $m = $view->m;
        $post = $_POST;

        $eventid = Valid::toInt($post, 'eventid');
        $regid = Valid::toInt($post, 'id');

        
        $delete = Valid::toStr($post, 'delete');
        $resend = Valid::toStr($post, 'resend');
        $worked = true;
        $m->eblog = $this->getEventBlog($eventid);
        
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
        if ($this->request->isAjax()) {
            $this->noLayouts();
            return $this->render('partials', 'events/regform');
        } else {
            return $this->render('events', 'register');
        }
    }

}
