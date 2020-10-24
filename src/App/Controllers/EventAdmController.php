<?php

namespace App\Controllers;

/**
 * @author Michael Rynn
 */
use App\Models\Event;
use WC\Valid;
use Soundasleep\Html2Text;

class EventAdmController extends BaseController
{

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;
    use \App\Link\EventOps;
    use \App\Link\RevisionOp;
    
    protected function getAllowRole(): string
    {
        return 'Admin';
    }

    protected function display($evt)
    {
        $view = $this->getView();

        $model = $view->m;
        $model->evt = $evt;
        $id = $evt->id;
        if ($id > 0) {
            $model->rego = $this->getRegoMail($id);
        }
        return $this->render('events', 'edit');
    }

    protected function reglist($evt)
    {
        $model = $this->getViewModel();
        $blog = $this->getBlogAndRevision($evt->blogid, $evt->revisionid);
        $model->evt = $evt;
        $model->blog_title = $blog->title;
        $id = $evt->id;
        if ($id > 0) {
            $model->rego = $this->getRegoMail($id);
        }
        $model->actions = [
            "No" => "",
            "Mail" => "Add to mail job",
            "Remove" => "Remove from mail job",
            "Delete" => "Unregister"
        ];

        return $this->render('events', 'reglist');
    }

    public function postAction()
    {
        $post = $_POST;
        $id = Valid::toInt($post, 'id');
        if ($id > 0) {
            $event = Event::findFirstById($id);
            $event->fromtime = Valid::toDateTime($post, 'fromtime');
            $event->totime = Valid::toDateTime($post, 'totime');
            $event->slug = Valid::toStr($post, 'slug');
            $event->enabled = Valid::toBool($post, 'enabled');
            $event->reg_detail = $post['reg_detail'];
            $event->revisionid = Valid::toInt($post, 'revisionid');
            if ($event->update()) {
                $this->flash('Event updated');
            }
            $db = $this->db;
            foreach ($post as $key => $value) {
                if (substr($key, 0, 3) === 'chk') {
                    $regid = (int) substr($key, 3);
                    // ensure mail  job record exists
                    $sql = "insert into reg_mail(reg_id, mail) values ($regid,1)"
                            . " on duplicate key update mail=1";
                    $db->execute($sql);
                }
            }
            return $this->display($event);
        }
    }

    public function reglistpostAction()
    {
        $post = $_POST;
        $id = Valid::toInt($post, 'id');
        $actions = Valid::toStr($post, 'actions');

        if ($id > 0) {
            $db = $this->db;
           
                foreach ($post as $key => $value) {
                    if (substr($key, 0, 3) === 'chk') {
                        $regid = (int) substr($key, 3);
                        // ensure mail  job record exists
                        if ($actions === "Mail") {
                            $sql = "insert into reg_mail(reg_id, mail) values ($regid,1)"
                                    . " on duplicate key update mail=1";
                            $db->execute($sql);
                        }
                        else if ($actions === "Delete") {
                            $sql = "delete from register where id = $regid";
                            $db->execute($sql);
                        }
                        else if ($actions === "Remove") {
                            $sql = "delete from reg_mail where reg_id = $regid";
                            $db->execute($sql);
                        }
                    }
                }
            
            return $this->reglist(Event::findFirstById($id));
        }
    }

    function reglistAction($id)
    {
        return $this->reglist(Event::findFirstById($id));
    }

    function editAction($id)
    {
        return $this->display(Event::findFirstById($id));
    }

}
