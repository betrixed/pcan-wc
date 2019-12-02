<?php
namespace WC;

use WC\DB\Blog;
use WC\DB\Event;
use WC\DB\RegEvent;
use WC\Valid;

//! Front-end processorg

class Register extends Controller {
    // Display Event blog with new register info
    function newReg($f3, $args) {
        
        if (!UserSession::https($f3)) {
            return;
        }
        $this->captchaView();
        
        $view = $this->view;
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
        $view->eblog = count($result) > 0 ? $result[0] : null;

        /* if event in the past don't allow */
        
        $view->content = 'events/register.phtml';
        $view->assets(['bootstrap', 'register-js']);
        
        if ((count($result) > 0) && (Valid::now() > $view->eblog['fromTime'])){
            $view->eblog = null;
        }
        $view->register = new RegEvent();
        $view->register['people'] = 0;
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
        $this->captchaView();
        $view = $this->view;
        $view->content = 'events/register.phtml';
        $view->assets(['bootstrap', 'register-js']);
        $view->eblog = null;
        if (!empty($regid)) {
            $rec = RegEvent::byId($regid);
            // Get the record 
          
            $eventId = $rec['eventid'];
            if ($code !== $rec['linkcode']) {
                $view->register = new RegEvent();
                $view->register['people'] = 0;
            } else {
                $result = Event::getEventBlog($eventId);
                $view->eblog = count($result) > 0 ? $result[0] : null;
                $view->register = $rec;
            }
        }
        echo $view->render();
    }
    function regPost($f3, $args) {
        $view = $this->view;
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
            if (!empty($email)) {
                $name = $fname . ' ' . $lname;
                $view->userName = $name;
                $view->publicUrl = $f3->get('domain');
                $view->editUrl = '/reglink/' . $rec['linkcode'] . '/' . $rec['id'];

                $textMsg = TagViewHelper::render('events/signup_text.txt');
                $htmlMsg = TagViewHelper::render('events/signup_html.phtml');
                $mailer = new SwiftMail();
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
        $view->eblog = count($result) > 0 ? $result[0] : null;
        $view->register = $rec;
        $view->layout = 'events/regform.phtml';

        echo $view->render();
    }
}
