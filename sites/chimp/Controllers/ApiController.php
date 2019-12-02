<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Chimp\Controllers;

use Chimp\Models\Mchimp;
use Chimp\Models\Mclist;
use Chimp\Api;

class ApiController extends \Pcan\Controllers\BaseController {
    
    public function delmemberAction()
    {
        
    }
    
    public function updateMember($member)
    {
        $mchimp = Mchimp::findFirstByemail($member->email_address);
        // Only new in the mass synchronize
        if (!$mchimp)
        {
            $mchimp = new Mchimp();
            $mchimp->mcid = $member->id;
            $mchimp->email = $member->email_address;
            $mchimp->created_at = $member->timestamp_opt;
            $mchimp->changed_at = $member->last_changed;
            $mchimp->status =  $member->status;
            $mchimp->listId = $member->list_id;
            $merge = $member->merge_fields;
            $mchimp->name = $merge->FNAME;
            $mchimp->surname = $merge->LNAME;
            $mchimp->info = $merge->MMERGE5;
            $mchimp->phone1 = $merge->MMERGE14;
            $mchimp->phone2 = $merge->MMERGE15;

            // additional merge fields added 
            $mchimp->memberType = $merge->MMERGE3;
            $mchimp->financial = $merge->MMERGE4;
            $mchimp->interests = $merge->MMERGE6;

            switch (strtolower($merge->MMERGE7))
            {
                case 'yes': $mchimp->volunteer = 1;
                    break;
                case 'no': $mchimp->volunteer = 0;
                    break;
                default:
                    if (strlen($merge->MMERGE7) > 0)
                    {
                        $mchimp->interests .= ' ' . $merge->MMERGE7;
                    }
            }

            $mchimp->position = $merge->MMERGE8;
            $mchimp->organisation = $merge->MMERGE9;
            $mchimp->address1 = $merge->MMERGE10;
            $mchimp->suburb = $merge->MMERGE11;
            $mchimp->state = $merge->MMERGE12;
            $mchimp->postcode = $merge->MMERGE13;


            // addition merge fields



            $mchimp->save();
        }
        else {
            // update only additional merge fields added 
            $merge = $member->merge_fields;
            $mchimp->memberType = $merge->MMERGE3;
            $mchimp->financial = $merge->MMERGE4;
            $mchimp->interests = $merge->MMERGE6;

            switch (strtolower($merge->MMERGE7))
            {
                case 'yes': $mchimp->volunteer = 1;
                    break;
                case 'no': $mchimp->volunteer = 0;
                    break;
                default:
                    if (strlen($merge->MMERGE7) > 0)
                    {
                        $mchimp->interests .= ' ' . $merge->MMERGE7;
                    }
            }

            $mchimp->position = $merge->MMERGE8;
            $mchimp->organisation = $merge->MMERGE9;
            $mchimp->address1 = $merge->MMERGE10;
            $mchimp->suburb = $merge->MMERGE11;
            $mchimp->state = $merge->MMERGE12;
            $mchimp->postcode = $merge->MMERGE13;
            //$mchimp->listId = $member->list_id;
            $mchimp->update();
        }  
    }
    public function indexAction()
    {
        try {
            $this->buildAssets();
            
            $response = $this->apiGet("lists", []);
            
            
            $lists = json_decode($response->body);
            
            // get id of first list
            $first = $lists->lists[0];
            $id = $first->id;
            $stats = $first->stats;
            
            $total = $stats->member_count + $stats->unsubscribe_count + $stats->cleaned_count;
            
            $list = Mclist::findFirstBylistId($id);
            
            if (!$list)
            {
                $list = new Mclist();
                $list->listId = $id;
                $list->listName = $first->name;
                $list->members = $stats->member_count;
                $list->cleaned = $stats->cleaned_count;
                $list->unsubscribed = $stats->unsubscribe_count;
                $list->save();
            }
            else {
                $list->members = $stats->member_count;
                $list->cleaned = $stats->cleaned_count;
                $list->unsubscribed = $stats->unsubscribe_count;
                $list->save();       
            }
            
            // get members and pageinate
            
            $pageSize = 50;
            $offset = 0;
            //$flags = 0;
            while($offset < $total)
            {
                $response = $this->apiGet("lists/$id/members", [
                    'offset' => $offset,
                    'count' => $pageSize
                ]);
                
                /*if ($offset > 0)
                    $flags = FILE_APPEND;
                 file_put_contents($this->config->logDir . "mchimp.json", $response->body, $flags);
                 */
                $members = json_decode($response->body);

                $mlist = $members->members;

                $this->view->lists = $mlist;

                foreach($mlist as $member)
                {
                    $this->updateMember($member);
                }
                
                $offset = $offset + $pageSize;
            }
        }
        catch ( \Exception  $ex)
        {
            $this->flash->error($ex->getMessage());
            $this->view->lists = [];
        }
    }
    
    public function apiGet($uri, $params = []) 
    {
        
        $api = new Api();
        $response = $api->doCurl('GET', $uri, $params);        
        return $response;
    }
};