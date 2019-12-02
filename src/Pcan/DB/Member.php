<?php

namespace Pcan\DB;

use WC\Valid;
use WC\DB\Server;
/**
 * Description of member
 *
 * @author michael
 */
class Member extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'member', NULL, 1.0e8); // 100 second
    }

    static public function byId($id) {
        $result = new Member();
        return $result->load(['id = ?', $id]);
    }

    static public function byPhone($fname, $lname, $phone) {
        $result = new Member();
        return $result->load(['fname = ? and lname = ? and phone = ?', $fname, $lname, $phone]);
    }
    

    static public function viewOrderBy($view, $orderby)
    {
        if (is_null($orderby))
        {
            $orderby = 'name-alt';
        }
        $alt_list = array(
            'mdate' => 'mdate',
            'cdate' => 'cdate',
            'name' => 'name',
            'surname' => 'surname',
            'email' => 'email',
            'phone' => 'phone',
            'city' => 'city',
            'status' => 'status'
        );
        $col_arrow = array(
            'mdate' => '',
            'cdate' => '',
            'name' => '',
            'surname' => '',
            'email' => '',
            'phone' => '',
            'city' => '',
            'status' => ''
        );  
        switch($orderby)
        {
            default:
            case 'name':
                $alt_list['name'] = 'name-alt';
                $col_arrow['name'] = '&#8595;';
                $order_field = 'M.fname asc';
                break;
            case 'mdate':
                $alt_list['mdate'] = 'mdate-alt';
                $col_arrow['mdate'] = '&#8595;';
                $order_field = 'M.last_update asc';
                break;
            case 'cdate':
                $alt_list['cdate'] = 'cdate-alt';
                $col_arrow['cdate'] = '&#8595;';
                $order_field = 'M.create_date asc';
                break;
            case 'surname':
                $alt_list['surname'] = 'surname-alt';
                $col_arrow['surname'] = '&#8595;';
                $order_field = 'M.lname asc';
                break;
            case 'email':
                $alt_list['email'] = 'email-alt';
                $col_arrow['email'] = '&#8595;';
                $order_field = 'ME.email_address asc';              
                break;
            case 'phone':
                $alt_list['phone'] = 'phone-alt';
                $col_arrow['phone'] = '&#8595;';
                $order_field = 'M.phone asc';              
                break;
            case 'city':
                $alt_list['city'] = 'city-alt';
                $col_arrow['city'] = '&#8595;';
                $order_field = 'M.city asc';              
                break;
            case 'status':
                $alt_list['status'] = 'status-alt';
                $col_arrow['status'] = '&#8595;';
                $order_field = 'email_status asc';              
                break;
            case 'name-alt':
                $col_arrow['name'] = '&#8593;';
                $order_field = 'M.fname desc';
                break;   
            case 'surname-alt':
                 $col_arrow['surname'] = '&#8593;';
                 $order_field = 'M.lname desc';
                 break;    
            case 'mdate-alt':
                 $col_arrow['mdate'] = '&#8593;';
                 $order_field = 'M.last_update desc';
                 break;       
            case 'cdate-alt':
                $col_arrow['mdate'] = '&#8593;';
                $order_field = 'M.last_update desc';
                break;    
            case 'email-alt':
                $col_arrow['email'] = '&#8593;';
                $order_field = 'ME.email_address desc';
                break; 
            case 'phone-alt':
                $col_arrow['phone'] = '&#8593;';
                $order_field = 'M.phone desc';
                break;  
            case 'city-alt':
                $col_arrow['city'] = '&#8593;';
                $order_field = 'M.city desc';
                break; 
            case 'status-alt':
                $col_arrow['status'] = '&#8593;';
                $order_field = 'email_status desc';
                break;                        
        }
        $view->orderalt = &$alt_list;
        $view->orderby = $orderby;
        $view->col_arrow = &$col_arrow;
        return $order_field;
    }
    /**
     * 
     * @param string $email
     * @return array [ member, member_email]
     */
    static function  byEmail($email) {
        $me = MemberEmail::byEmail($email);
        if ($me !== false) {
            $mbr = static::byId($me['memberid']);
            return [$mbr, $me];
        }
        return [false, false];
    }
    
    /** update status field on record id
     * 
     * @param type $id
     * @param type $status
     */
    static function setStatus($id, $status) {
        $sql = "update member_email set status = :stat where id = :id";
        $db = Server::db();
        $db->exec($sql, ['id' => $id, 'stat' => $status]);
    }
    static function getEmails($id) {
        $sql = "select e.* from member_email e where e.memberid = :mid";
        $db = Server::db();
        $results = $db->exec($sql, ['mid' => $id]);
        if ($results) {
            return $results;
        } else {
            return [];
        }
    }  
    static function getDonations($id) {
        $sql = "select d.* from donation d where d.memberid = :mid order by d.member_date";
        $db = Server::db();
        $results = $db->exec($sql, ['mid' => $id]);
        if ($results) {
            return $results;
        } else {
            return [];
        }
    }   
    /**
     * 
     * @param array $post
     * @return array[ record, bool (isNew) ]
     * This does not write back to database.
     */
    static function assignPost(&$post)
    {
        $errorList = [];
        
        $id = Valid::toInt($post,'id',0);
        
        $fname = Valid::toStr($post,'fname',"");
        $lname = Valid::toStr($post,'lname',"");
        $phone = Valid::toPhone($post,'phone',"");
        if (!empty($phone)) {

            if (!Valid::has_GEnDigits($phone,8)) {
                $errorList[] = ['phone','Phone no. must have at least 8 digits'];
            }
        }
        
        if ($id > 0) {   
            $rec = Member::byId($id);
            $isNew = false;
        }
        else {
            // see if record exists by phone
            $rec = Member::byPhone($fname, $lname, $phone);
            
            if ($rec === false) {
                $rec = new Member();
                $isNew = true;

            }
            else {
               $isNew = false;
            }
        }
        
        
        $rec['phone'] = $phone;
        $rec['fname'] = $fname;
        $rec['lname'] = $lname;          
        
        $rec['addr1'] = Valid::toStr($post,'addr1',"");
        $rec['addr2'] = Valid::toStr($post,'addr2',"");
        $rec['postcode'] = Valid::toStr($post,'postcode',"");
        $rec['city'] = Valid::toStr($post,'city',"");
        $rec['state'] = Valid::toStr($post,'state',"");
        $rec['country_code'] = Valid::toStr($post,'country_code',"");

        $rec['source'] = Valid::toStr($post,'source',"");
        $today = Valid::now();
        if ($isNew) {
            $rec['create_date'] = $today;
            $rec['phpjson'] = json_encode([]);
            $rec['status'] =  'no-email';
        }
        else {
            $rec['status'] = Valid::toStr($post,'status',"");
        }
        $rec['last_update'] = $today;  
        return [$rec, $isNew];
    }
}
