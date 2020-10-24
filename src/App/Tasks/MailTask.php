<?php

namespace App\Tasks;
use WC\Db\DbQuery;
use App\Models\Event;
use WC\WConfig;
use WC\SwiftMail;
use Soundasleep\Html2Text;
/**
 * Iterative Mail out task from reg_mail table
 *
 * @author michael
 */
class MailTask extends \Phalcon\Cli\Task 
{
    use \App\Link\RevisionOp;
    use \WC\Mixin\ViewPhalcon;
    
    private function flash(string $msg) {
        echo $msg . PHP_EOL;
    }
        private function error(string $msg) {
        echo "*** ERROR *** " . $msg . PHP_EOL;
    }
    
    // set $m->editUrl -  full edit link url
    private function sendLinkEmail($m) :bool {
        $rec = $m->register;
        $app = $this->app;
        $prefix = "https://" . $app->domain;
             
        $editUrl =$prefix . '/reglink/' . $rec->linkcode . '/' . $rec->id;
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

             }
             else {
                   $model->detail = null;
             }
            $params['m'] = $model;
            $params['app'] = $app;

            $htmlMsg = static::simpleView('events/signup_html', $params);
            $textMsg = Html2Text::convert( $htmlMsg );

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
        
        foreach($list as $rec){
            echo $rec->email . PHP_EOL;
            $event =  Event::findFirstById($rec->eventid);
           
            if (!empty($event)) {
                 $m->event = $event;
                 $m->eblog = $this->getBlogAndRevision($event->blogid, $event->revisionid);
                 $m->register = $rec;
                 echo $m->eblog->title . PHP_EOL;
                 if ($this->sendLinkEmail($m) ) {
                     echo "Success ! " . PHP_EOL;
                     $db = $this->db;
                     $db->execute("update reg_mail set mail = 0 where reg_id = " . $rec->id);
                     $mailcount += 1;
                 }
            }
        }
        echo "Emails sent = " . $mailcount . PHP_EOL;
    }
}
