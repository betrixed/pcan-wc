<?php


namespace Pcan;

/**
 * @author Michael Rynn
 */
use Pcan\DB\Event;
use WC\Valid;

class EventEdit extends Controller {
    public function beforeRoute() {
        if (!$this->auth()) {
            return false;
        }
    }
    
    function displayEvent($evt) {
        $view = $this->view;
        $view->assets(['bootstrap','DateTime']);
        $view->content = "events/edit.phtml";
        $view->evt = $evt;
        $id = $evt['id'];
        if ($id > 0) {
            $view->rego = Event::getRego($id);
        }
        echo $view->render();      
    }
    function evtpost($f3, $args) {
        $post = &$f3->ref('POST');
        $id = Valid::toInt($post,'id');
        if ($id > 0) {
            $event = Event::byId($id);
            $event['fromTime'] = Valid::toDateTime($post, 'fromTime');
            $event['toTime'] = Valid::toDateTime($post, 'toTime');
            $event['slug'] = Valid::toStr($post, 'slug');
            $event['enabled'] = Valid::toBool($post, 'enabled');
            if ($event->update()) {
                $this->flash('Event updated');
            }
            $this->displayEvent($event);
        }
    }
    function edit($f3, $args) {
        $eid = $args['id'];
        $this->displayEvent(Event::byId($eid));
    }
}
