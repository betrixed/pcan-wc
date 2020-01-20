<?php
namespace Pcan;

use Pcan\DB\Blog;
use Pcan\DB\Event;
use Pcan\DB\RegEvent;
use WC\Valid;
use WC\UserSession;
use WC\SwiftMail;
//! Front-end processorg

class Register extends Controller {
use Mixin\ViewPlates;
use Mixin\Captcha;
    // Display Event blog with new register info
    function newReg($f3, $args) {
        
        if (!UserSession::https($f3)) {
            return;
        }
         $view = $this->getView();
          
        $view->content = 'events/register.phtml';
        $view->assets(['bootstrap', 'register-js']);
        
        
             $m = $view->model;  
        $this->captchaView($m);

        $eventId = $args['id'];
        
        if (is_numeric($eventId)) {
             $result = Event::getEventBlog($eventId);
        }
        else {
            $sid = Event::getSlugId($eventId);
            if (count($sid) > 0) {
                $result = Event::getEventBlog($sid[0]['id']);
            } 
            else {
                $result=[];
            }
        }
        $m->eblog = count($result) > 0 ? $result[0] : null;

        /* if event in the past don't allow */

        if ((count($result) > 0) && (Valid::now() > $m->eblog['fromTime'])){
            $m->eblog = null;
        }
        $m->register = new RegEvent();
        $m->register['people'] = 0;
        echo $view->render();
    }
    
    private function error($msg) {
        $this->flash($msg);
    }
    
    function regEdit($f3, $args) {
        if (!UserSession::https($f3)) {
            return;
        }
        $regid = $args['id'];
        $code = $args['code'];
        $view = $this->getView();
         $view->content = 'events/register.phtml';
        $view->assets(['bootstrap', 'register-js']);
        
        $m = $view->model;
        
        $this->captchaView($m);

        $m->eblog = null;
        if (!empty($regid)) {
            $rec = RegEvent::byId($regid);
            // Get the record 
          
            $eventId = $rec['eventid'];
            if ($code !== $rec['linkcode']) {
                $m->register = new RegEvent();
                $m->register['people'] = 0;
            } else {
                $result = Event::getEventBlog($eventId);
                $m->eblog = count($result) > 0 ? $result[0] : null;
                $m->register = $rec;
            }
        }
        echo $view->render();
    }
    function regPost($f3, $args) {
        $view = $this->getView();
        $m = $view->model;
        $post = &$f3->ref('POST');
        
        $eventid = Valid::toInt($post,'eventid');
        $regid = Valid::toInt($post, 'id');
        
        $delete = Valid::toStr($post,'delete');
        $worked = true;
        
        if (!empty($regid)) {
            // Get the record 
            $rec = RegEvent::byId($regid);
            if (!empty($rec) && !empty($delete)) {
        // this record will be deleted
                $rec->erase();
                $rec = new RegEvent();
                $rec['eventid'] = $eventid;
                $this->flash('Previous registration deleted');
                $worked = false;
            }
        }
        else {
            $rec = new RegEvent();
            $rec['eventid'] = $eventid;
            $rec['created_at'] = Valid::now();
        }
        
        if ($worked) {
            $lname = Valid::toStr($post,'lname');
            $fname = Valid::toStr($post,'fname');
            $email = Valid::toEmail($post,'email');
            $people = Valid::toInt($post, 'people');
            $phone = Valid::toPhone($post, 'phone');
            if (empty($fname) && empty($lname)) {
                $this->error('Names should not be empty');
            }
            $rec['fname'] = $fname;
            $rec['lname'] = $lname;
            $rec['email'] = $email;
            $rec['phone'] = $phone;
            $rec['people'] = $people;


            try {
                if (empty($regid))
                {
                    $op = 'saved';
                    $rec->save();
                }               
                else {
                    $op = 'updated';
                    $rec->update();   
                }                
            } catch (Exception $ex) {
                $this->error('Failed to save register for event');
                $worked = false;
            }
            if ($worked) {
                $this->flash('Your registration was ' . $op);
            }
        }
        
        if ($worked) {
             $m->editUrl = '/reglink/' . $rec['linkcode'] . '/' . $rec['id'];
            if (!empty($email)) {
                $name = $fname . ' ' . $lname;
                
                $model = new \WC\WConfig(); 
                $model->editUrl = $m->editUrl;
                $model->userName = $name;
                $model->publicUrl = $f3->get('domain');

                $textMsg = static::renderView($model, 'events/signup_text.phtml');
                $htmlMsg = static::renderView($model, 'events/signup_html.phtml');
                $mailer = new  SwiftMail($this->f3);
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
        $result = Event::getEventBlog($eventId);
        $m->eblog = count($result) > 0 ? $result[0] : null;
        $m->register = $rec;
        $view->layout = $f3->get('AJAX') ? null : $view->layout;
        $view->content = 'events/regform.phtml';
        
        echo $view->render();
    }
}
