<?php
namespace GH;


use WC\DB\Server;
use WC\Valid;

use Pcan\DB\UserEvent;
use Pcan\DB\User;
use WC\UserSession;
use Pcan\DB\Member;
use Pcan\DB\MemberEmail;
use WC\SwiftMail;

use Chimp\DB\ChimpEntry;


class SignUp extends \Pcan\Controller {
use \Pcan\Mixin\ViewPlates;
use \Pcan\Mixin\Captcha;
    function errorSignup($msg)
    {
        $logger = new \Log('login.log');
        $logger->write('Fail Signup - ' . $msg);
        UserSession::flash( $msg, ['Unable to save'], 'danger');
        $this->signupView();
    }

    function signupPost($f3, $args)
    {
        $view = $this->getView();
        $m = $view->model;
        
        $post = &$f3->ref('POST');
        $verify = $this->captchaResult($post);
        if (!$verify['success']) {
            $this->errorSignup('Google error ' . $verify['errorcode']);
            return;
        }

        if (!$this->xcheckResult($post)) {
            $this->errorSignup('Not current token');
            return;
        }

        $email = Valid::toEmail($post, 'email', null);
        if (empty($email)) {
            $this->errorSignup('Invalid email');
            return;
        }
        list($member,$isNew) = Member::assignPost($post);

        if ($isNew) {
            list($mbr, $mbr_email) = Member::byEmail($email);
            if ($mbr !== false) {
                $m->mbr = $member;
                $m->email = $email;
                $this->errorSignup('Email already registered');
                return;
            }
        }
        else {
            
        }
        $saved = false;
        try {
            
            if ($isNew) {
                $member->save();
            }
            else {
                $member->update();
            }
            $mbr_email = new MemberEmail();
            $mbr_email['memberid'] = $member['id'];
            $mbr_email['email_address'] = $email;
            $mbr_email['status'] = null;
            $mbr_email->save();
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
             $this->errorPDO($e);
             return;
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
            $this->signupView();
            return;
        }
        $emailTo = $f3->get('secrets.mail.to');
        
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
            $this->signupView();
        } else {
            $this->errorSignup($isValid['errors']);
            return;
        }
    }
    private function errorPDO($e) {
        $err = $e->errorInfo;
        $this->flash($err[0] . ": " . $err[1], [$e->getMessage()]);
        $this->errorSignup("Signup Failed");
    }
    function signupView()
    {
        $f3 = $this->f3;
        if (!UserSession::https($f3)) {
            return;
        }
        $view = $this->view;
        $view->content = 'home/signup.phtml';
        $view->assets('bootstrap');
        
        $m  = $view->model;
        
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
        

        echo $view->render();
    }

    function signup($f3, $args)
    {
        if (!UserSession::https($f3)) {
            return;
        }
        $view = $this->getView();
        $m = $view->model;
        $m->email = '';
        $mbr = new Member();
        $mbr['state'] = 'NSW';
        $mbr['country'] = 'AU';
        $m->mbr = $mbr;

        $m->message = '';
        $this->signupView();
    }   

}