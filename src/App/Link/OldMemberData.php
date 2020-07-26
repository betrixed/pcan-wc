<?php

namespace App\Link;

use WC\Valid;
use WC\Db\Server;
use WC\Db\DbQuery;
/**
 * Description of member
 *
 * @author michael
 */
trait MemberData {
    /** 
     * set orderby fields of model $m, given current value index
     */
    static public function orderBy($m, $orderby)
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
                $orderby = 'name';
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
        $m->orderalt = $alt_list;
        $m->orderby = $orderby;
        $m->col_arrow = $col_arrow;
        return $order_field;
    }
    
    static function memberNamePhone($fname, $lname, $phone) {
        return Member::findFirst(
            ['fname = ? and lname = ? and phone = ?', 
            [$fname, $lname, $phone], 
            [Column::BIND_PARAM_STR, Column::BIND_PARAM_STR, Column::BIND_PARAM_STR]
            ]);
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
            $rec = static::memberNamePhone($fname, $lname, $phone);
            
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
