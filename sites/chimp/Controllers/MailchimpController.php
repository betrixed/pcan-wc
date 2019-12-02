<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Chimp\Controllers;

use Pcan\Models\PageInfo;

use Chimp\Models\Mchimp;
use Chimp\Models\Mclist;
use Chimp\Models\Donation;
use Chimp\Forms\McqueryForm;
use Chimp\Forms\DonateForm;
use Chimp\Forms\MemberForm;
use Chimp\Api;

use Phalcon\Http\Response;
use Phalcon\Mvc\View;

use Pcan\Plugins\JQueryForm;
use Pcan\Plugins\DateTimePicker;

class MailchimpController extends \Pcan\Controllers\BaseController {
    
    protected function listPageNum($numberPage, $pageRows, $orderby) {
        $start = ($numberPage - 1) * $pageRows;
        //SQL_CALC_FOUND_ROWS
        $sql = "select SQL_CALC_FOUND_ROWS b.* "
                . " from mchimp b"
                . " order by " . $orderby
                . " limit " . $start . ", " . $pageRows;

        $db = $this->getDb();
        $stmt = $db->query($sql);

        $stmt->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        $results = $stmt->fetchAll();

        $cquery = $db->query("SELECT FOUND_ROWS()");
        $cquery->setFetchMode(\Phalcon\Db::FETCH_NUM);
        $maxrows = $cquery->fetch();
        
        return new PageInfo($numberPage, $pageRows, $results, $maxrows[0]);
    }
    
    static function  addWhere(&$where, $clause)
    {
        if (strlen($where) > 0)
              $where .= " and ";
        $where .= $clause;
    }
    
    public function getDonations($mcid)
    {
        $sql = "select b.* "
                      . " from donation b"
                      . " where b.mcid = '" . $mcid . "'"
                      . " order by created_at ";

        $db = $this->getDb();
        $stmt = $db->query($sql);

        $stmt->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        $results = $stmt->fetchAll();

        $this->view->donate = $results;      
    }
    
    public function donateAction($donateId)
    {
        $this->buildAssets();
        if($this->request->isPost())
        {
            $req = $this->request;
            $form = new DonateForm();
            if (!$form->isValid($req->getPost()))
            {
                foreach($form->getMessages() as $message){
                        $this->flash->error($message->getMessage());
                }    
            }
            else {
                $this->view->form = $form;
                $donate = Donation::FindFirst($donateId);
                $this->view->memid = $donate->mcid;
                $donate->amount = $req->get('amount');
                $donate->purpose = $req->get('purpose');
                $donate->member_date = $req->get('member_date');
                if ($donate->save() == false)
                {
                    foreach ($donate->getMessages() as $message) {
                        echo $message;
                    }
                }             
            }
        }
        else {
            $donate = Donation::FindFirst($donateId);
            if (is_object($donate))
            {
                $form = new DonateForm($donate);
                $this->view->form = $form;
                $this->view->memid = $donate->mcid;
            }
        }
    }
    /**
     * 
     * @param type $editId ; using mailchimp hash key for email address
     */
    public function listRequest($editId)
    {
        $this->view->editId = $editId;
       
        $mperson = Mchimp::findFirstByMcid($editId);
        
        $this->view->person = $mperson;
        $this->view->form = null;
        if ($mperson) {
            $this->getDonations($mperson->mcid);
  
            if ($mperson->status == 'subscribed' || $mperson->status == 'no-email')
            {
                $form = new DonateForm();
                $form->get('mcid')->setDefault($mperson->mcid);
                $this->view->form = $form;
            }
            //$member = new MemberForm($mperson);
            //$this->view->null = $member;
            $this->view->data = null;
            /*
            $api = new Api();
            $response = $api->doCurl('GET', 'lists/' . $mperson->listId . '/members/' . $mperson->mcid);
            
            /*
            $data = $response->body;//json_decode($response->body);
            
            $this->view->data =  $data;// json_encode($data, JSON_PRETTY_PRINT);
            
            
            $data = json_decode($response->body);
            $this->view->data = json_encode($data, JSON_PRETTY_PRINT);
            */
             
        }
        else
            $this->view->donate = null;
        
        // access mailchimp information
       
               
    }
    public function listAction($editId)
    {
        $this->buildAssets();
        $this->view->memid = $editId;
        if ( $this->request->isPost())
        {
            $req = $this->request;
            
            $formid = $req->get("formid");
            
            if ($formid == "donation")
            {
                $form = new DonateForm();
                if (!$form->isValid($req->getPost()))
                {
                    foreach($form->getMessages() as $message){
                            $this->flash->error($message->getMessage());
                        }
                    $this->listRequest($editId);
                }
                else {


                    $donate = new Donation();
                    $donate->mcid = $req->get('mcid');
                    $mperson = Mchimp::findFirstByMcid($donate->mcid);
                    $this->view->person = $mperson;
                    $this->view->member = new MemberForm($mperson);
                    
                    $donate->amount = $req->get('amount');
                    $donate->purpose = $req->get('purpose');
                    $donate->member_date = $req->get('member_date');
                    if ($donate->save() == false)
                    {
                        foreach ($donate->getMessages() as $message) {
                            echo $message;
                        }
                    }
                    $this->getDonations($donate->mcid);
                    $this->view->form = $form;
                }
            }
            
        }
        else {
            $this->listRequest($editId);
            
        }
        //$editId = $id;
        
        
    }
    
    public function buildAssets()
    {
        parent::buildAssets();
        $this->assetArray(DateTimePicker::assets());
        $this->assetArray(JQueryForm::assets());
    }
    public function editAction()
    {
        
        $this->buildAssets();
        
        $form = new MemberForm();
        $this->view->member = $form;    
        if ( $this->request->isPost() )
        {
            $req = $this->request;
            $submit_value = $req->get('edit-member');
            if ($submit_value == 'edit')
            {
                
            }
            elseif ($submit_value == 'delete')
            {
                
            }
        }
    }
    public function newAction()
    {
        $this->buildAssets();
        $form = new MemberForm();
        $this->view->form = $form;    
        
        if ( $this->request->isPost() )
        {
            
            if (!$form->isValid($_POST)) 
            {
                $messages = $form->getMessages();

                foreach ($messages as $message) {
                    $this->flash->error( $message);
                }
            }
            else {   
                $mchimp = new Mchimp();
                $req = $this->request;
                
                $mchimp->name = $req->get('name');
                $mchimp->surname = $req->get('surname');
                $mchimp->info = $req->get('info');
                $mchimp->phone1 = $req->get('phone1');
                $mchimp->phone2 = $req->get('phone2');
                $mchimp->status =  $req->getPost("statustype");  
                $mchimp->listId = '';
                
                if ($mchimp->status == "no-email")
                {
                    // create a 'fake' email for hash purposes
                    // see if it exists in DB using appended digits
                    $instanceCount = 1;
                    $email_prefix = strtolower($mchimp->name . "." . $mchimp->surname);
                    $email_domain = "@not.real.domain";
                    
                    $test_email = $email_prefix . $instanceCount . $email_domain;
                    
                    $mchimp->email = $test_email;
                }
                else {
                    $mchimp->email = $req->getPost('email','email');
                }
                $mchimp->mcid = md5(strtolower($mchimp->email));
                if ($mchimp->save() == false)
                {
                    foreach ($mchimp->getMessages() as $message) {
                        echo $message;
                    }
                }
                else {
                    $this->response->redirect('/' . $this->myController . 'query');
                    $this->view->disable();
                }
            }
                 
        } 
    }
    
    static public function squote($str)
    {
        return str_replace("'", "''", $str);
    }
    public function memberlistAction()
    {
        $this->buildAssets();
        $req = $this->request;
        if ($req->isPost())
        {
            $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
            //$this->view->pick("blog/event");
            $form = new McqueryForm();
            if (!$form->isValid($_POST)) 
            {
                $messages = $form->getMessages();

                foreach ($messages as $message) {
                    echo $message, "<br>";
                }
            }
            else {        
                $name = $req->getPost("name");
                $name_sel = $req->getPost('name_sel');
                
                $surname = $req->getPost("surname");
                $surname_sel = $req->getPost('surname_sel');
                
                $status = $req->getPost("statustype");
                $member = $req->getPost("membertype");
                
                $orderby = $req->getPost("orderby");
                $order_field = Mchimp::indexOrderBy($this->view, $orderby);
                
                $hasPhone = $req->getPost("hasPhone");
                
                $sql = "select SQL_CALC_FOUND_ROWS b.* from mchimp b";
                $where = '';
                if (strlen($status) > 0)
                {
                    $this::addWhere($where, "status = '$status'");
                }
                if (strlen($name) > 0)
                {
                    $name = $this::squote($name);
                    if ($name_sel == 'contain')
                    {
                        $this::addWhere($where, "name like '%$name%'");
                    }
                    else if ($name_sel == 'start')
                    {
                        $this::addWhere($where, "name like '%name%'");
                    }
                    else 
                        $this::addWhere($where, "name = '$name'");
                }
                if (strlen($surname) > 0)
                {
                    $surname = $this::squote($surname);
                    if ($surname_sel == 'contain')
                    {
                        $this::addWhere($where, "surname like '%$surname%'");
                    }
                    else if ($surname_sel == 'start')
                    {
                        $this::addWhere($where, "surname like '$surname%'");
                    }
                    else
                        $this::addWhere($where, "surname = '$surname'");
                }      
                if (strlen($hasPhone) > 0)
                {
                    if ($hasPhone == 'yes')
                    {
                        $clause = "(((phone1 is not null) AND (phone1 <> ''))" . 
                                "OR ((phone2 is not null) AND (phone2 <> '')))";
                    }
                    else {
                        $clause = "(((phone1 is null) OR (phone1 = ''))" . 
                                "AND ((phone2 is null) OR (phone2 = '')))";
                    }
                    $this::addWhere($where, $clause);
                }
                if (strlen($member) > 0)
                {
                    switch ($member)
                    {
                        case "none":
                            $this::addWhere($where, "not exists (select * from donation d where d.mcid = b.mcid)");
                            break;
                        case "current":
                            $exists = "exists (select * from donation d where "
                                . " d.mcid = b.mcid and purpose = 'member' and DATEDIFF(CURDATE(),d.member_date) <= 366)";
                            $this::addWhere($where, $exists);
                            break;
                        case "past":
                            $exists = "exists (select * from donation d where "
                                . " d.mcid = b.mcid and purpose = 'member' and DATEDIFF(CURDATE(),d.member_date) > 366)"
                                . " and not exists (select * from donation d where "
                                . " d.mcid = b.mcid and purpose = 'member' and DATEDIFF(CURDATE(),d.member_date) <= 366)";
                            $this::addWhere($where, $exists);
                            break;
                        case "sponsor":
                            $exists = "exists (select * from donation d where "
                                . " d.mcid = b.mcid and purpose = 'sponsor'";
                            $this::addWhere($where, $exists);
                            break;
                        default:
                            
                            
                    }
                }
                if (strlen($where) > 0)
                    $sql .= " where " . $where;
                if (strlen($order_field) > 0)
                    $sql .= " order by " . $order_field;
                
                $submit = $req->getPost('submit');

                try {
                    $db = $this->getDb();
                    $stmt = $db->query($sql);
                    $ip =  $stmt->getInternalResult();
                    $total_columns = $ip->columnCount();
                    $columns = [];
                    for($i = 0; $i < $total_columns; $i++)
                    {
                        $meta = $ip->getColumnMeta($i);
                        $columns[] = $meta['name'];
                    }                
                    
                    if ($submit == "download")
                    {
                        $resp = new Response();
                        $resp->setContentType('text/csv', 'utf-8');  
                        $resp->setHeader('Content-Disposition','attachment; filename=data.csv');
                        $output = fopen('php://memory', 'wb+');

                        fputcsv($output, $columns);
                        
                        $stmt->setFetchMode(\Phalcon\Db::FETCH_NUM);
                        while ($row = $stmt->fetchArray()) {
                            fputcsv($output, $row);
                        }
                        $data = stream_get_contents($output,-1,0);
                        $resp->setContent($data);
                        fclose($output);
                        $resp->send();
                        $this->view->disable();
                    }
                    else 
                    {
                        $stmt->setFetchMode(\Phalcon\Db::FETCH_OBJ);
                        $results = $stmt->fetchAll();

                        $cquery = $db->query("SELECT FOUND_ROWS()");
                        $cquery->setFetchMode(\Phalcon\Db::FETCH_NUM);
                        $maxrows = $cquery->fetch();
                        $this->view->page = new PageInfo(1, $maxrows[0], $results, $maxrows[0]);
                    }
                    
                }
                catch( Exeception $e) {
                    $this->flash->error("Errors " . $e->getMessage());
                }       
            }
        }
       
    }
    public function queryAction()
    {
        $this->buildAssets();
        $req = $this->request;
        
        if ($req->isPost())
        {
        }
        else {
            $this->view->form = new McqueryForm();
            $orderby = null;
            $order_field = Mchimp::indexOrderBy($this->view, $orderby);
        }
        
    }
    
    public function setViewParameters($view)
    {
        parent::setViewParameters($view);
        $view->myController = $this->module . "/mail/";
    }
    public function indexAction()
    {
        $this->buildAssets();
        $numberPage = $this->request->getQuery("page", "int");
        $orderby = $this->request->getQuery('orderby');
        $order_field = Mchimp::indexOrderBy($this->view, $orderby);
        if (is_null($numberPage)) {
            $numberPage = 1;
        } else {
            $numberPage = intval($numberPage);
        }
        $this->view->orderby = $orderby;
        $this->view->page = $this->listPageNum($numberPage, 12, $order_field);   
    }
}
