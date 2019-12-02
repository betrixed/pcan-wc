<?php
namespace SBO;
/**
 * @author Michael Rynn
 */
use WC\Valid;
use WC\UserSession;
use WC\SwiftMail;
use WC\TagViewHelper;

class Hire extends \WC\Controller
{
    protected $url = '/contact/hire/';

    /** Return html/text of view */
    public function render($rec, $isSub = false)
    {
        $f3 = $this->f3;
        $view = $this->view;
        $view->url = $this->url;
        $view->rec = $rec;
        
        $this->captchaView();
        $this->xcheckView();
        $view->assets(['bootstrap','DateTime']);
        if ($isSub) {
            $view->sub = 1;
            $view->layout = 'form_hire/edit.phtml';
        } else {
            $view->sub = 0;
            $view->content = 'form_hire/edit.phtml';
        }
        echo $view->render();
    }

    private function readonly() {
        $view = $this->view;
        $view->title = "Sent";
        $view->url = $this->url;
        $view->assets(['bootstrap']);
        $view->content = 'form_hire/sent.phtml';
        echo $view->render();
    }
    
    public function newRec($f3, $args)
    {
        if (!UserSession::https($f3)) {
            return;
        }
        $req = &$f3->ref('REQUEST');
        $this->render(new FormHire(), isset($req['sub']));
        return false;
    }

    public function view($f3, $args)
    {
        if (!$this->auth()) {
            return false;
        }
        $id = $args['pid'];
        $view = $this->view;
        $view->rec = FormHire::findById($id);
        $view->title = "Hire";
        $view->url = $this->url;
        $view->assets(['bootstrap']);
        $view->content = 'form_hire/sent.phtml';
        echo $view->render();
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
            UserSession::reroute($this->url . 'new');
            return;
        }
        $rec = empty($id) ? new FormHire() : FormHire::findById($id);
        FormHire::setFromPost($post, $rec);
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

        if ($isValid) {
            $view = $this->view;
            $view->rec = $rec;
            $textMsg = TagViewHelper::render('email/hire_sbo.txt');
            $htmlMsg = TagViewHelper::render('email/hire_sbo.phtml');
            $mailer = new SwiftMail();
            $msg = [
                "subject" => 'Hire SBO',
                "text" => $textMsg,
                "html" => $htmlMsg
            ];
            $mok = $mailer->send( $msg );
            if ($mok['success']) {
                $this->flash('Sent OK');
                $this->readonly();
                return;
            }
            else {
               $this->flash('Send Error ', $mok['errors']); 
            }
        }
        $this->render($rec);
    }

}
