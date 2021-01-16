<?php

namespace WC\Controllers;

/**
 * @author Michael Rynn
 */
use WC\Models\Contact;
use WC\UserSession;
use WC\Valid;
use WC\SwiftMail;
use Phalcon\Mvc\Controller;
use WC\Assets;
use WC\App;



class EmailFormController extends Controller {

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Captcha;

    protected $url = '/contact/email/';

    private function makeForm($isSub = false) {
        
        $m = $this->getViewModel();
        $m->title = "Email";
        $m->formid = "msgform";
        $m->url = $this->url;
        $m->blog = null; 
        $this->captchaView($m);
        $this->xcheckView($m);
        $req = $this->request;
        $isAjax = $req->isAjax();
        
        if ($isAjax) {
            $this->noLayouts();
            $m->sub = 1;
            return $this->render('form', 'email');
        } else  {
            if ($isSub) {
                $m->sub = 1;
                $this->noLayouts();
                return $this->render('form', 'email');
            } else {
                $m->sub = 0;
                return $this->render('form', 'email');
            }
        }
    }

    public function emailAction( ) {
        if ($this->need_ssl()) {
            return $this->secure_connect();
        }
        $m = $this->getViewModel();
        $m->rec = new Contact();;
        $isSub = $this->request->getQuery('sub','int',0);

        return $this->makeForm($isSub);
    }

    static function postToRec($post, $rec) {
        $rec->name = Valid::toStr($post, 'name', '');
        $rec->body = Valid::toStr($post, 'body', '');
        $rec->email = Valid::toEmail($post, 'email', '');
        $rec->telephone = Valid::toPhone($post, 'telephone', '');
        $rec->senddate = Valid::now();
    }

    public function errorEmail($msg, $rec) {
        $this->flash($msg);
        $m = $this->getViewModel();
        $m->rec = $rec;
        return $this->makeForm(true);
    }

    public function postAction() {
        $post = $_POST;
        $id = Valid::toInt($post, 'id', null);
        $isSub = Valid::toInt($post, 'sub', 0);
        $req = $this->request;
        if (!$req->isAjax()) {
            $isValid = false;
        }
        if (!empty($id)) {
            $rec = Contact::findFirstById($id);
        } else {
            $rec = new Contact();
        }
        static::postToRec($post, $rec);

        
        if (!$this->xcheckResult($post)) {
            return $this->errorEmail("Bad csfr token", $rec);
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

            $model = new \WC\WConfig(); 
            $model->rec = $rec;
            $model->link = $this->https_url();
            
            $params['m'] = $model;
            $params['app'] = $this->app;
            $textMsg = static::simpleView('form/mail_text',$params);
            $htmlMsg = static::simpleView('form/mail_html',$params);

            $mailer = new SwiftMail($this->app);
            $msg = [
                "subject" => 'Website Contact',
                "text" => $textMsg,
                "html" => $htmlMsg
            ];
            $mok = $mailer->send($msg);

            if ($mok['success']) {
                $this->flash('email sent');
                $m = $this->getViewModel();
                $m->rec = $rec;
                $this->noLayouts();
                $this->user_session->wipe();
                return $this->render('form', 'sent');;
            }
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
        }
        return $this->makeForm($isSub);
    }

}
