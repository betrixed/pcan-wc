<?php
namespace SBO;

/**
 * @author Michael Rynn
 */

use WC\Valid;
use WC\UserSession;
use WC\TagViewHelper;
use WC\SwiftMail;

class Player extends \WC\Controller
{

    protected $url = '/contact/player/';

    /** Return html/text of view */
    public function render($rec, $isSub = false)
    {
        $f3 = $this->f3;
        $view = $this->view;
        $this->captchaView();
        $this->xcheckView();
        $view->assets(['bootstrap']);
        $view->rec = $rec;
        
        if ($isSub) {
            $view->sub = 1;
            $view->layout = 'form_player/edit.phtml';
            
        } else {
            $view->sub = 0;
            $view->content = 'form_player/edit.phtml';
            
        }
        echo $view->render();
    }
    
    function viewSent($rec,$isSub) {
        $f3 = $this->f3;
        $view = $this->view;
        $view->assets(['bootstrap']);
        $view->rec = $rec;
        if ($isSub) {
            $view->sub = 1;
            $view->layout = 'form_player/sent.phtml';
        } else {
            $view->sub = 0;
            $view->content = 'form_player/sent.phtml';
        }
        echo $view->render();
    }
    public function newRec($f3, $args)
    {
        if (!UserSession::https($f3)) {
            return;
        }
        $req = &$f3->ref('REQUEST');
        $this->render(new FormPlayer(), isset($req['sub']));
        return false;
    }

    public function view($f3, $args)
    {
        if (!$this->auth()) {
            return false;
        }
        $id = $args['pid'];
        $this->render(FormPlayer::findById($id), false);
        return false;
    }

    public function post($f3, $args)
    {
        $post = &$f3->ref('POST');
        $id = Valid::toInt($post, 'id', null);

        $isValid = $this->xcheckResult($post);
        if ($isValid) {
            $isValid = $this->captchaResult($post)['success'];
        }
        else {
            $this->flash('Invalid token');
        }
        if (!$isValid) {
            $f3->reroute($this->url . 'new');
            return;
        }
        
        $rec = empty($id) ? new FormPlayer() : FormPlayer::findById($id);
        FormPlayer::setFromPost($post, $rec);
        
        $isSub = Valid::toInt($post,'sub',0);
        $test = isset($post['history']) ? $post['history'] : null;
        if ($test && (Valid::hasURL($test, $msg) || Valid::hasBitcoin($test, $msg))) {
             $this->flash($msg);
             $isValid = false;
        }
        else {
            try {
                if (empty($id)) {
                    $rec->save();
                    $id = $rec['id'];
                } else {
                    $rec->update();
                }
            } catch (\PDOException $e) {
                $err = $e->errorInfo;
                $this->flash($err[0] . ": " . $err[1]);
                $isValid = false;
            }
        }

        if ($isValid) {
            $view = $this->view;
            $view->rec = $rec;
            $textMsg = TagViewHelper::render('email/join_sbo.txt');
            $htmlMsg = TagViewHelper::render('email/join_sbo.phtml');
            $mailer = new SwiftMail();
            $msg = [
                "subject" => 'Join Orchestra',
                "text" => $textMsg,
                "html" => $htmlMsg
            ];
            $mok = $mailer->send( $msg );
            if ($mok['success']) {
                $this->flash('Sent OK');
                $this->viewSent($rec,$isSub);
            }
            else {
               $this->flash('Send Error ', $mok['errors']); 
               $this->render($rec,$isSub);
            }
        }
        else {
            $this->render($rec,$isSub);
        }
        
    }

}
