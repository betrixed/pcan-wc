<?php

/*
See the "licence.txt" file at the root "private" folder of this site
*/

namespace Chimp\Controllers;

use Chimp\Models\Mchimp;
use Chimp\Models\Mclist;
use Chimp\Forms\MemberForm;
use Phalcon\Http\Response;
use Phalcon\Mvc\View;
use Chimp\Api;

class MemberController extends \Pcan\Controllers\BaseController {
    
    public function getDonations()
    {
        $sql = "select b.* , m.mcid, m.name, m.surname"
                      . " from donation b join mchimp m on m.mcid = b.mcid" 
                      . " order by created_at ";

        $db = $this->getDb();
        $stmt = $db->query($sql);

        $stmt->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        $results = $stmt->fetchAll();

        $this->view->donate = $results;      
    }
    
    public function donateAction()
    {
        $this->buildAssets();
        
        $req = $this->request;
        if (!$req->isPost())
        {
            $this->getDonations();
        }
        else {
            $this->view->donate = null; 
        }
    }
    
    public function editAction($id)
    {  
        $this->buildAssets();
        $mperson = Mchimp::findFirstByMcid($id);
        if ($mperson != false)
        {
            $req = $this->request;
            
            if ($req->isPost())
            {
                $form = new MemberForm();
                if (!$form->isValid($req->getPost()))
                {
                    foreach($form->getMessages() as $message){
                            $this->flash->error($message->getMessage());
                        }
                    $this->listRequest($id);
                    $this->view->person = $mperson;
                    $this->view->member = $form;
                }
                else {
                    $optype = $req->get("edit-member");
                    if ($optype == 'edit')
                    {
                        //* update with new values
                        $mperson->name = $req->get("name");
                        $mperson->surname = $req->get("surname");
                        // email and mcid can't be updated yet
                        $mperson->phone1 = $req->get("phone1");
                        $mperson->phone2 = $req->get("phone2");
                        $mperson->info = $req->get("info");
                        $mperson->memberType = $req->get("memberType");
                        $mperson->changed_at = date('Y-m-d H:i:s');
                        // status can't be updated yet 
                        if ($mperson->save()  == false)
                        {
                            foreach ($mperson->getMessages() as $message) {
                                echo $message , "\n";
                            }
                            $this->view->person = $mperson;
                            $this->view->member = $form;
                        }
                        else {
                            $this->response->redirect('/' . $this->module . "mailchimp/list/".$id);
                            $this->view->disable();
                        }
                    }
                    elseif ($optype == 'delete')
                    {
                        /* mail chimp API says 
                         * DELETE html operation
                         *  -- /lists/{list_id}/members/{subscriber_hash}
                         */
                        if ($mperson->status == 'deleted')
                        {
                            $this->flash->information('Record already marked deleted');
                        }
                        else {
                            $api = new Api();
                            $response = $api->doCurl('DELETE', 'lists/' . $mperson->listId . '/members/' . $mperson->mcid);

                            // if no exception thrown, then the DELETE was accepted, and this member record is to be deleted

                            // at the moment the delete is to be done by setting status to deleted

                            $mperson->status = 'deleted';
                            $mperson->changed_at = date('Y-m-d H:i:s');
                            if ($mperson->save() == false)
                            {
                                foreach ($mperson->getMessages() as $message) {
                                    echo $message , "\n";
                                }
                                $this->view->person = $mperson;
                                $this->view->member = $form;
                            }
                            else {
                                // cannot view this record now -- back to search
                                $this->response->redirect("/mailchimp/query");
                                $this->view->disable();
                            }
                        }
                    }
                }
            }
            else {
                $this->view->person = $mperson;
                $form = new MemberForm($mperson);
                $id = $form->get('email');
                $id->setAttribute('readonly','readonly');
                $id = $form->get('status');
                //$id->setAttribute('readonly','readonly');
                $id->setAttribute('disabled','true');
                
                $this->view->member = $form;
            }
        }
        
    }
}