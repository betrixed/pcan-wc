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
    

    private function  getEventBlog($eid)
    {
        $db = $this->dbq;
                
$sql=<<<EOD
select e.fromTime, e.toTime, e.enabled, e.id as eventid, e.reg_detail,
 b.* from event e 
 join blog b on b.id = e.blogid
 where e.id = :eid
EOD;
        $result = $db->arraySet($sql, 
                ['eid' => $eid], 
                ['eid' => Column::BIND_PARAM_INT]);
        if (!empty($result)) {
            return $result[0];
        }
        else 
            return null;
    }
    
    private function  getSlugId($slug) {
        $db = $this->dbq;
        $sql=<<<EOD
select e.fromTime, e.toTime, e.enabled, e.id as eventid, 
 b.* from event e 
 join blog b on b.id = e.blogid
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
        }
        else 
            return null;
    }

    function newRegAction($eventId) {
        
        if ($this->need_ssl()) {
            return $this->secure_connect();
        }

        
        $view = $this->getView();
        $m = $view->m;
        
       /* $view->content = 'events/register.phtml';
        $view->assets(['bootstrap', 'register-js']);
        */
        
        $this->captchaView($m);
        $this->xcheckView($m);
       
        if (is_numeric($eventId)) {
             $result = $this->getEventBlog($eventId);
        }
        else {
            $result = $this->getSlugId($eventId);
        }
        $m->eblog = $result;

        $m->register = new Register();
        $m->register->people = 0;
        return $this->render('events','register');
    }
    
    private function error($msg) {
        $this->flash($msg);
    }
    
    function editAction($code,$regid) {
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
        return $this->render('events','register');
    }
    function regPostAction() {
        $view = $this->getView();
        $m = $view->m;
        $post = $_POST;
        
        $eventid = Valid::toInt($post,'eventid');
        $regid = Valid::toInt($post, 'id');
        
        $delete = Valid::toStr($post,'delete');
        $worked = true;
        
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
        }
        else {
            $rec = new Register();
            $rec->eventid = $eventid;
            $rec->created_at = Valid::now();
        }
        
        if ($worked) {
            $lname = Valid::toStr($post,'lname');
            $fname = Valid::toStr($post,'fname');
            $email = Valid::toEmail($post,'email');
            $people = Valid::toInt($post, 'people');
            $phone = Valid::toPhone($post, 'phone');
            if (empty($fname) || empty($lname) || empty($email)) {
                $this->error('Name and Email required');
            }
            $rec->fname = $fname;
            $rec->lname = $lname;
            
            if ($email !== $rec->email) {
                $rec->email = $email;
                $rec->linkcode = md5(strtolower($rec->email) . $rec->eventid . strtolower( $rec->fname) . strtolower( $rec->lname));
            }          
            $rec->phone = $phone;
            $rec->people = $people;

            try {
                if (empty($regid))
                {
                    $op = 'created';
                    $rec->create();
                }               
                else {
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
        
        if ($worked) {
            $app = App::instance();
            $m->editUrl = '/reglink/' . $rec->linkcode . '/' . $rec->id;
            if (!empty($email)) {
                $name = $fname . ' ' . $lname;
                
                $model = new WConfig(); 
                $model->link = UserSession::urlPrefix() . $m->editUrl;
                $model->userName = $name;
                $model->domain = $app->organization;
                $model->detail = $rec->reg_detail ?? null;
                
                $params['m'] = $model;
                $params['app'] = $app;
                
                $htmlMsg = static::simpleView( 'events/signup_html', $params);
                $textMsg = (new Html2Text($htmlMsg))->getText();
                
                $mailer = new  SwiftMail();
                $msg = [
                    "subject" => 'Event registration for ' . $view->publicUrl,
                    "text" => $textMsg,
                    "html" => $htmlMsg,
                    "to" => [
                        "email" => $email,
                        "name" => $name
                        ]
                ];
                $isValid = $mailer->send($msg);
                if ($isValid['success']) {
                    $this->flash('Link sent to your email');
                } else {
                    $this->errorSignup($isValid['errors']);
                    return;
                }
            }
        }
        $m->eblog = $this->getEventBlog($eventid);
        $m->register = $rec;
        if ($this->request->isAjax()) {
            $this->noLayouts();
            return $this->render('partials','events/regform');
        }
        else {
            return $this->render('events','register');
        }
    }
}
