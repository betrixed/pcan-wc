<?php


namespace App\Controllers;

/**
 * @author Michael Rynn
 */
use App\Models\Event;
use WC\Valid;
use App\Link\EventOps;

class EventAdmController extends \Phalcon\Mvc\Controller {
use \WC\Mixin\ViewPhalcon;
use \WC\Mixin\Auth;
    
    protected function getAllowRole() : string {
        return 'Admin';
    }
    protected function display($evt) {
        $view = $this->getView();
        
        $model = $view->m;
        $model->evt = $evt;
        $id = $evt->id;
        if ($id > 0) {
            $model->rego = EventOps::getRego($id);
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
