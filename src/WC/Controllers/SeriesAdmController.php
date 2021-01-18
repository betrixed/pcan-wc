<?php

namespace WC\Controllers;


use WC\Valid;
use WC\Models\Series;
/**
 * Controller for series Table
 *
 * @author Michael Rynn
 */
class SeriesAdmController extends BaseController {
    use \WC\Mixin\Auth;
    use \WC\Mixin\ViewPhalcon;

    public function getAllowRole() {
        return 'Editor';
    }

    public function indexAction() {
        $m = $this->getViewModel();
        $m->series = Series::find();
      
        return $this->render('series', 'index');
    }

    public function editAction(int $id) {
         $m = $this->getViewModel();
         $m->rec = Series::findFirstByid($id);
         return $this->render('series', 'edit');
    }
    
    public function postAction() {
        $post = $this->getPost();
        $id = Valid::toInt($post, "id");
        $name = Valid::toStr($post, "name");
        $desc = Valid::toStr($post, "description");
        $tag = Valid::toStr($post, "tag");
        
        if ($id===0) {
            $rec = new Series();
        }
        else {
            $rec = Series::findFirstByid($id);
        }
        $rec->name = $name;
        $rec->description = $desc;
        $rec->tinytag = $tag;
        $result = ($id===0) ? $rec->create() : $rec->update();
        if ($result) {
            $this->flash("Record saved");
        }
        
        $dispatch = $this->dispatcher;
        
        return $this->reroute('/admin/series/edit/' . $rec->id);
        
    }
    public function newAction() {
        $m = $this->getViewModel();
        $m->rec = new Series();
        return $this->render('series', 'edit');
    }
}
