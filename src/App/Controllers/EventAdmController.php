<?php


namespace App\Controllers;

/**
 * @author Michael Rynn
 */
use App\Models\Event;
use WC\Valid;


class EventAdmController extends BaseController {
use \WC\Mixin\ViewPhalcon;
use \WC\Mixin\Auth;
use \App\Link\EventOps;    

    protected function getAllowRole() : string {
        return 'Admin';
    }
    protected function display($evt) {
        $view = $this->getView();
        
        $model = $view->m;
        $model->evt = $evt;
        $id = $evt->id;
        if ($id > 0) {
            $model->rego = $this->getRego($id);
        }
       return $this->render('events', 'edit');      
    }
    public function postAction() {
        $post = $_POST;
        $id = Valid::toInt($post,'id');
        if ($id > 0) {
            $event = Event::findFirstById($id);
            $event->fromtime = Valid::toDateTime($post, 'fromtime');
            $event->totime = Valid::toDateTime($post, 'totime');
            $event->slug = Valid::toStr($post, 'slug');
            $event->enabled = Valid::toBool($post, 'enabled');
            $event->reg_detail =  $post['reg_detail'];
            $event->revisionid = Valid::toInt($post, 'revisionid');
            if ($event->update()) {
                $this->flash('Event updated');
            }
            return $this->display($event);
        }
    }
    function editAction($id) {
        return $this->display(Event::findFirstById($id));
    }
}
