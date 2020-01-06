<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */

use Pcan\DB\Contact;
use WC\UserSession;
use WC\Valid;
use WC\SwiftMail;

class EmailForm extends Controller {
use Mixin\ViewPlates;
use Mixin\Captcha;

    protected $url = '/contact/email/';

    public function __construct($f3, $args) {
        parent::__construct($f3,$args);
        $view = $this->getView();
        $view->assets(['bootstrap']);
        $m = $view->model;
        $m->url = $this->url;
        $m->title = "Email";
    }
    private function render($isSub = false) {
        $view = $this->view;
        $this->captchaView($view->model);
        $this->xcheckView($view->model);
        $view->content = 'form/email';
        if ($isSub) {
             $view->sub = 1;
             $view->layout = null;
        }
        else {
            $view->sub = 0;

        }
        echo $view->render();
    }
    
    private function readonly() {
        $view = $this->view;
        $view->content = 'form/sent';
        echo $view->render();
    }
    public function email($f3, $args) {
        if (!UserSession::https($f3)) {
            return;
        }

        $view = $this->view;
        $m = $view->model;
        $m->rec = new Contact();
        $req = &$f3->ref('REQUEST');
        
        $this->render(isset($req['sub']));
    }
    public function view($f3, $args) {
        if (!$this->auth()) {
            return false;
        }

        $id = intval($args['cid']);
        $view = $this->view;
        $m = $view->model;
        $m->rec = Contact::byId($id);
        $req = &$f3->ref('REQUEST');
        
        $this->render();
    }
    
    static protected function postToRec(&$post, &$rec) {
        
        $rec['name'] = Valid::toStr($post, 'name', '');
        $rec['body'] = Valid::toStr($post, 'body', '');
        $rec['email'] = Valid::toStr($post, 'email', '');
        $rec['telephone'] = Valid::toStr($post, 'telephone', '');
        $rec['senddate'] = Valid::now();
    }
    public function errorEmail($msg,  $rec) {
        $this->flash($msg);
        $view = $this->view;
        $view->model = $rec;
        echo $this->render();
    }
    public function post($f3, $args) {
        $post = &$f3->ref("POST");
        $id = Valid::toInt($post, 'id', null);
        $isSub = Valid::toInt($post, 'sub', 0);
        
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
            return $this->errorEmail("Captcha error", $rec);
        }
        $isValid = false;
        
        $test = isset($post['body']) ? $post['body'] : null;
        if ($test && (Valid::hasURL($test, $msg) || Valid::hasBitcoin($test, $msg))) {
            return $this->errorEmail($msg, $rec);
        }
        try {
            if (!empty($id)) {
                $rec->update();
            } else {
                $rec->save();
            }
            $errors = [];
            
            $view = $this->view;
            $m = $view->model;
            $m->rec = $rec;
            $m->link = UserSession::getURL($f3);
            $textMsg = TagViewHelper::render('form/mail_text.txt');
            $htmlMsg = TagViewHelper::render('form/mail_html.phtml');
            $mailer = new SwiftMail($f3);
            $msg = [
                "subject" => 'Website Contact',
                "text" => $textMsg,
                "html" => $htmlMsg
            ];
            $mok = $mailer->send($msg);
            
            if ($mok['success']) {
                $this->flash('email sent');
                $this->readonly();
                return;
            }
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
        } 
        $this->render($isSub);
    }

}
