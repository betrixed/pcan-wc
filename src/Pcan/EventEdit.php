<?php


namespace Pcan;

/**
 * @author Michael Rynn
 */
use Pcan\DB\Event;
use WC\Valid;

class EventEdit extends Controller {
use Mixin\ViewPlates;
use Mixin\Auth;
    
    function displayEvent($evt) {
        $view = $this->getView();
        $view->assets(['bootstrap','DateTime']);
        $view->content = "events/edit";
        $model = $view->model;
        $model->evt = $evt;
        $id = $evt['id'];
        if ($id > 0) {
            $model->rego = Event::getRego($id);
        }
        echo $view->render();      
    }
    function evtpost($f3, $args) {
        $post = &$f3->ref('POST');
        $id = Valid::toInt($post,'id');
        if ($id > 0) {
            $event = Event::byId($id);
            $event['fromtime'] = Valid::toDateTime($post, 'fromtime');
            $event['totime'] = Valid::toDateTime($post, 'totime');
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
