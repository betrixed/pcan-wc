<?php
namespace WC\Controllers;


use WC\Db\Server;
use WC\Valid;
use WC\App;

use WC\Models\UserEvent;
use WC\Models\User;
use WC\UserSession;
use WC\Models\Member;
use WC\Models\MemberEmail;
use WC\SwiftMail;
//use WC\Models\ChimpEntry;
use Phalcon\Mvc\Controller;
use WC\Link\LinkData;

class SignupController extends Controller {
use \WC\Mixin\ViewPhalcon;
use \WC\Mixin\Captcha;

    function errorSignup($msg)
    {
        UserSession::flash( $msg, ['Unable to save'], 'danger');
        return $this->signupView();
    }

    function postAction()
    {
        $view = $this->getView();
        $m = $view->m;
        
        $post = $_POST;
        $verify = $this->captchaResult($post);
        if (!$verify['success']) {
            return $this->errorSignup('Google error ' . $verify['errorcode']);
        }

        if (!$this->xcheckResult($post)) {
            return $this->errorSignup('Not current token');
        }

        $email = Valid::toEmail($post, 'email', null);
        if (empty($email)) {
            return $this->errorSignup('Invalid email');
        }
        
        LinkData::assignPost($post,$m);
        
        list($member,$isNew) = LinkData::assignMember($m);

        if ($isNew) {
            list($mbr, $mbr_email) = LinkData::byEmail($email);
            if (!empty($mbr)) {
                $m->mbr = $member;
                $m->email = $email;
                return $this->errorSignup('Email already registered');
            }
        }
        
        $saved = false;
        try {
            
            if ($isNew) {
                $member->create();
            }
            else {
                $member->update();
            }
            $mbr_email = new MemberEmail();
            $mbr_email->memberid = $member->id;
            $mbr_email->email_address = $email;
            $mbr_email->status = null;
            $mbr_email->create();
            /*
            $eid = $mbr_email['id'];
            $entry = ChimpEntry::addMemberEmail($eid);
            if ($entry !== false) {
                $mbr_email['status'] = $entry['status'];
                $mbr_email->update();
            }
            */
            $saved = true;
        }
        catch (\PDOException $e) {
             return $this->errorSignup($e->getMessage());
        }
        if ($saved) {
            $this->flash("Record saved");
        }
        else {
            // re-edit same data
            // show any errors
            $m->email = $email;
            $m->title = "Edit Errors";
            $m->mbr = $member;
            return $this->signupView();
        }
        $sec = $this->app->getSecrets('mail');
        $emailTo = $sec['to'];
        
        $textMsg = "New E-List website signup " . $email;
        $mailer = new SwiftMail();
        $msg = [
            "subject" => 'E-List signup',
            "text" => $textMsg,
            "to" => [
                "email" => $emailTo['email'],
                "name" => $emailTo['name']
                ]
        ];
        $isValid = $mailer->send($msg);
        if ($isValid['success']) {
            $this->flash('successful notify');
            return $this->signupView();
        } else {
            return  $this->errorSignup($isValid['errors']);
        }
    }
    private function errorPDO($e) {
        $err = $e->errorInfo;
        $this->flash($err[0] . ": " . $err[1], [$e->getMessage()]);
        $this->errorSignup("Signup Failed");
    }
    protected function signupView()
    {
        $view = $this->view;
        
        $m  = $view->m;
        
        $m->title = "New Subscribe";
        $states = [
                '' => '',
                'NSW' => 'New South Wales', 
                'QLD' => 'Queensland',
                'VIC' => 'Victoria',
                'TAS' => 'Tasmania',
                'ACT' => 'Aust. Capital Territory',
                'NT' => 'Northern Territory',
                'WA' => 'Western Australia',
                'SA' => 'South Australia'
            ];
        ksort($states);
        $m->states = $states;
        $m->countries = ['AU' => 'Australia'];
        $this->captchaView($m);
        $this->xcheckView($m);

        return $this->render('index','signup');
    }

    function signupAction()
    {
        if ($this->need_ssl()) {
            return $this->secure_connect();
        }
        $view = $this->getView();
        $m = $view->m;
        $m->email = '';
        $mbr = new Member();
        $mbr->state = 'NSW';
        $mbr->country = 'AU';
        $m->mbr = $mbr;
        $m->message = '';
        return $this->signupView();
    }   

}