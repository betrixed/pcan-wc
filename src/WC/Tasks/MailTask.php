<?php

namespace WC\Tasks;

use WC\Db\DbQuery;
use WC\Models\{Event, EmailTpl, EmailGroup};
use WC\{Valid, SwiftMail, WConfig};
use Soundasleep\Html2Text;

/**
 * Iterative Mail out task from reg_mail table
 *
 * @author michael
 */
class MailTask extends \Phiz\Cli\Task {

    use \WC\Link\RevisionOp;
    use \WC\Mixin\ViewPhalcon;
    use \WC\Link\EmailData;
    
    private function flash(string $msg) {
        echo $msg . PHP_EOL;
    }

    private function error(string $msg) {
        echo "*** ERROR *** " . $msg . PHP_EOL;
    }

    private function sendTplEmail($m) {
        $tp = $m->template;
        $group = $m->group;
        $plates = $m->plates;
        $rec = $m->register;

        $data = new WConfig();
        if (!empty($rec->email)) {
            $data->fname = $rec->fname;
            $data->lname = $rec->lname;
            $email_code = $this->link_encode($rec->id, $tp->id);
            $data->link = $m->prefix . '/emailid/' . $email_code;
            
            //$data->link = $m->prefix . '/emailid/' . $rec->linkcode . '/' . $rec->id;
            $data->userName = $rec->fname . ' ' . $rec->lname;
            $data->domain = $m->organization;
            $data->date = Valid::today();
        }

        $params['m'] = $data;
        $params['app'] = $this->app;

        $htmlMsg = static::simpleView($plates->view, $params, $plates);
        $textMsg = Html2Text::convert($htmlMsg);
        $mailer = new SwiftMail($this->app);
        $msg = [
            "subject" => $tp->subject,
            "text" => $textMsg,
            "html" => $htmlMsg,
            "to" => [
                "email" => $rec->email,
                "name" => $data->userName
            ]
        ];
        $isValid = $mailer->send($msg);
        if ($isValid['success']) {
            $this->flash('Email sent');
            return true;
        } else {
            $this->error($isValid['errors']);
            return false;
        }
    }

    // set $m->editUrl -  full edit link url
    private function sendLinkEmail($m): bool {
        $rec = $m->register;
        $app = $this->app;
        $prefix = "https://" . $app->domain;
        $editUrl = $prefix . '/reglink/' . $rec->linkcode . '/' . $rec->id;
        if (!empty($rec->email)) {
            $event = $m->event;
            $name = $rec->fname . ' ' . $rec->lname;
            // model for email template
            $model = new WConfig();
            $model->link = $editUrl;
            $model->userName = $name;

            $model->domain = $app->organization;
            if (!empty($event->reg_detail)) {
                $model->detail = $event->reg_detail;
            } else {
                $model->detail = null;
            }
            $params['m'] = $model;
            $params['app'] = $app;

            $htmlMsg = static::simpleView('events/signup_html', $params);
            $textMsg = Html2Text::convert($htmlMsg);

            $mailer = new SwiftMail($this->app);
            $msg = [
                "subject" => 'Event registration for ' . $m->eblog->title,
                "text" => $textMsg,
                "html" => $htmlMsg,
                "to" => [
                    "email" => $rec->email,
                    "name" => $name
                ]
            ];
            $isValid = $mailer->send($msg);
            if ($isValid['success']) {
                $this->flash('Email sent');
                return true;
            } else {
                $this->error($isValid['errors']);
                return false;
            }
        }
        // this was for view render
        // $m->editUrl = $editUrl; 
    }

    public function eventAction() {
        $dbq = $this->dbq;
        $sql = "select R.* from register R join reg_mail RM on R.id = RM.reg_id"
                . " where RM.mail = 1";
        $list = $dbq->objectSet($sql);
        $event = null;
        $m = new WConfig();
        $mailcount = 0;

        foreach ($list as $rec) {
            echo $rec->email . PHP_EOL;
            $event = Event::findFirstById($rec->eventid);

            if (!empty($event)) {
                $m->event = $event;
                $m->eblog = $this->getBlogAndRevision($event->blogid, $event->revisionid);
                $m->register = $rec;
                echo $m->eblog->title . PHP_EOL;
                if ($this->sendLinkEmail($m)) {
                    echo "Success ! " . PHP_EOL;
                    $db = $this->db;
                    $db->execute("update reg_mail set mail = 0 where reg_id = " . $rec->id);
                    $mailcount += 1;
                }
            }
        }
        echo "Emails sent = " . $mailcount . PHP_EOL;
    }

    public function batchAction(array $params) {
        $dbq = $this->dbq;
        $sql = "select R.* from register R join reg_mail RM on R.id = RM.reg_id"
                . " where RM.mail = 1 and RM.email_tpl_id = :tid";
        $tid = intval($params[0]);

        $dbq->bindParam('tid', $tid);
        $list = $dbq->queryOA($sql);

        $m = new WConfig();
        $mailcount = 0;

        $tp = EmailTpl::findFirstById($tid);
        $group = EmailGroup::findFirstById($tp->groupid);
        $plates = $this->setupPlates($tp, $group);

        $m->template = $tp;
        $m->group = $group;
        $m->plates = $plates;

        $app = $this->app;
        $m->prefix = "https://" . $app->domain;
        $m->organization = $app->organization;

        foreach ($list as $rec) {
            echo $rec->email . PHP_EOL;


            if (!empty($tp)) {

                $m->register = $rec;

                if ($this->sendTplEmail($m)) {
                    echo "Success ! " . PHP_EOL;
                    $db = $this->db;
                    $db->execute("update reg_mail set mail = 0 where reg_id = " . $rec->id . " and email_tpl_id = $tid");
                    $mailcount += 1;
                }
            }
        }
        echo "Emails sent = " . $mailcount . PHP_EOL;
    }

}
