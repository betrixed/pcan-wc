<?php

namespace WC\Controllers;

use WC\Models\{
    Register,
    EmailTpl,
    EmailGroup
};
use WC\{
    Valid,
    SwiftMail,
    WConfig
};
use Soundasleep\Html2Text;

/**
 * 
 *
 * @author michael
 */
class UnregController extends BaseController {

    use \WC\Mixin\ViewPhalcon;
    use \WC\Link\EmailData;

    private $url = '/emailid/';

    public function dellinkAction($token, $code) {
        $m = $this->getViewModel();
        $m->code = $code;
        $post = $_POST;
        $cancel = Valid::toStr($post,'cancel');
        if (!empty($cancel)) {
            $this->flash("No action done");
            return $this->noAccess();
        }
        $recids = $this->link_decode($code);
        if (!empty($recids)) {
            $rego = Register::findFirstById($recids[0]);
            if (!empty($rego)) {
                $email = $rego->email;
                $sql = 'select R.id from register R where R.email = :em';
                $qry = $this->dbq;
                $qry->bindParam('em', $email);
                $results = $qry->queryOA($sql);
                foreach ($results as $obj) {
                    $dsql = 'delete from reg_mail where reg_id = ' . $obj->id;
                    $this->db->exec($dsql);
                }
                $dsql = "delete from register where email = '$email'";
                $this->db->exec($dsql);
                $this->flash("All records deleted");
                return $this->render('email_group', 'no_records');
            }
        }
        return $this->noAccess();
    }

    public function regdetailAction($token, $code) {
        $m = $this->getViewModel();

        $recids = $this->link_decode($code);
        if (!empty($recids)) {
            $m->rego = Register::findFirstById($recids[0]);
            $m->code = $code;
            $m->url = $this->url;
            if (!empty($m->rego)) {
                return $this->render('email_group', 'regdetail');
            }
        }
        return $this->noAccess();
    }

    public function sendlinkAction() {
        $post = $_POST;
        $code = Valid::toStr($post, 'code');
        $email = Valid::toEmail($post, 'email');
        if (!empty($email)) {
            $m = $this->getViewModel();
            $m->email = $email;
            $m->url = $this->url;
            $m->code = $code;

            $app = $this->app;
            $qry = $this->dbq;
            $sql = <<<EOD
select  R.id from register R where R.email = :em
EOD;
            $qry->bindParam('em', $email);
            $results = $qry->queryOA($sql);
            if (empty($results)) {

                $this->flash('No records found for this email');
                return $this->render('email_group', 'no_records');
            }
            $rid = $results[0]->id;
            $reg = Register::findFirstById($rid);
            $link = "https://" . $app->domain . "/unreg/" . $reg->linkcode
                    . "/" . $m->code;
            if (!empty($reg)) {

                $msg_body = "<p>Here is your access url<br> "
                        . "<a href='$link'>$link</a><br>"
                        . "<br></p>";
                $text_body = Html2Text::convert($msg_body);
                $msg = [
                    "subject" => "Register access token",
                    "html" => $msg_body,
                    "text" => $text_body,
                    "to" => [
                        "email" => $email,
                        "name" => $reg->fname . ' ' . $reg->lname
                    ]
                ];
                $mailer = new SwiftMail($app);
                $isValid = $mailer->send($msg);
                if ($isValid['success']) {
                    $this->flash('Email sent');
                } else {
                    $this->error($isValid['errors']);
                }
            }
            return $this->render('email_group', 'temp_reg');
        }
        return $this->noAccess();
    }

    public function decodeAction($eid) {
        $data = $this->link_decode($eid);
        if (!empty($data)) {
            $m = $this->getViewModel();
            $m->code = $eid;
            $m->rec = Register::findFirstById($data[0]);
            $m->template = EmailTpl::findFirstById($data[1]);
            if (empty($m->rec) || empty($m->template)) {
                return $this->noAccess();
            }
            $m->url = "/emailid/";
            $m->group = EmailGroup::findFirstById($m->template->groupid);
            return $this->render('email_group', 'unreg');
        }
        return $this->noAccess();
    }

}
