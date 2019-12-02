<?php

/**
 * @author Michael Rynn
 */

use WC\DB\Contact;
use WC\UserSession;

class EmailForm extends \WC\Controller {

    protected $url = '/contact/email/';

    private function render($isSub = false) {
        $view = $this->view;
        $view->title = "Email";
        $view->url = $this->url;
        $view->assets(['bootstrap','custom']);
        $this->captchaView();
        $this->xcheckView();
        
        if ($isSub) {
             $view->sub = 1;
             return TagViewHelper::render('form/email.phtml',$view);
        }
        else {
            $view->sub = 0;
            $view->content = 'form/email.phtml';
            echo $view->render();
        }
        
    }
    
    private function readonly() {
        $view = $this->view;
        $view->title = "Email";
        $view->url = $this->url;
        $view->assets(['bootstrap']);
        $view->content = 'form/sent.phtml';
        echo $view->render();
    }
    public function email($f3, $args) {
        if (!UserSession::https($f3)) {
            return;
        }
        $view = $this->view;
        $view->content = 'form/email.phtml';
        
        $view->rec = new Contact();
        $req = &$f3->get('REQUEST');
        
        echo $this->render(isset($req['sub']));
    }
    public function edit($f3, $args) {
        if (!UserSession::https($f3)) {
            return;
        }
        $id = intval($args['cid']);
        $view = $this->view;
        $view->rec = Contact::byId($id);
        $req = &$f3->get('REQUEST');
        
        echo $this->render();
    }
    
    static protected function postToRec(&$post, &$rec) {
        
        $rec['name'] = Valid::toStr($post, 'name', '');
        $rec['body'] = Valid::toStr($post, 'body', '');
        $rec['email'] = Valid::toStr($post, 'email', '');
        $rec['telephone'] = Valid::toStr($post, 'telephone', '');
        $rec['sendDate'] = Valid::now();
    }
    public function errorEmail($msg, &$rec) {
        $this->flash($msg);
        $view = $this->view;
        $view->rec = &$rec;
        echo $this->render();
    }
    public function post($f3, $args) {
        $post = &$f3->ref("POST");
        $id = Valid::toInt($post, 'id', null);
        if (!empty($id)) {
            $rec = Contact::byId($id);
        } else {
            $rec = new Contact();
        }  
        static::postToRec($post, $rec);

        if (!$this->xcheckResult($post)) {
            return $this->errorEmail("Bad token", $rec);
        }
        
        if (!$this->captchaResult($post)) {
            return $this->errorMail("Captcha error", $rec);
        }
        $isValid = false;
        try {
            if (!empty($id)) {
                $rec->update();
            } else {
                $rec->save();
            }
            $errors = [];
            
            $view = $this->view;
            $view->rec = &$rec;
            $textMsg = TagViewHelper::render('form/mail_text.txt');
            $htmlMsg = TagViewHelper::render('form/mail_html.phtml');
            $mailer = new SwiftMail();
            $msg = [
                "subject" => 'Website Contact',
                "text" => $textMsg,
                "html" => $htmlMsg
            ];
            $mok = $mailer->send($msg);
            
            if ($mok['success']) {
                $this->flash('email sent');
            }
            else {
                //TODO: Email fail
            }
            $this->readonly();
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
        } 
    }

}
