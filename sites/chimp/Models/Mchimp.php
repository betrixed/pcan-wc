<?php
/*
See the "licence.txt" file at the root "private" folder of this site
*/
namespace Chimp\Models;

use Phalcon\Mvc\Model;

class Mchimp extends Model {
    
    public $id;
    
    public $email;
    
    public $mcid;
    
    public $listId;
    
    public $phone1; //MMERGE14
    
    public $phone2; //MMERGE15
    
    public $name;
    
    public $surname; 
    
    public $info; //MMERGE5
    
    public $status;
    
    public $created_at;
    
    public $changed_at;
    
    public $memberType; // MERGE3 // apparently not used
    
    public $financial; // MERGE4
    
    public $interests; // MERGE6
    
    public $volunteer; // MERGE7
    
    public $position; // MERGE8
    
    
    public $organisation; // MERGE9
    
    public $address1; // MERGE10
    
    public $suburb; // MERGE11
    
    public $state; // MERGE12
    
    public $postcode; //MERGE13
    
    
    
    static public function indexOrderBy($view, $orderby)
    {
        if (is_null($orderby))
        {
            $orderby = 'surname';
        }
        $alt_list = array(
            'date' => 'date',
            'name' => 'name',
            'surname' => 'surname',
            'email' => 'email',
            'status' => 'status'
        );
        $col_arrow = array(
            'date' => '',
            'name' => '',
            'surname' => '',
            'email' => '',
            'status' => ''
         );  
        switch($orderby)
        {
            case 'name':
                $alt_list['name'] = 'name-alt';
                $col_arrow['name'] = '&#8595;';
                $order_field = 'b.name asc';
                break;
            case 'date':
                $alt_list['date'] = 'date-alt';
                $col_arrow['date'] = '&#8595;';
                $order_field = 'b.created_at asc';
                break;
            case 'surname':
                $alt_list['surname'] = 'surname-alt';
                $col_arrow['surname'] = '&#8595;';
                $order_field = 'b.surname asc';
                break;
             case 'email':
                $alt_list['email'] = 'email-alt';
                $col_arrow['email'] = '&#8595;';
                $order_field = 'b.email asc';
                break;     
            case 'status':
                $alt_list['status'] = 'status-alt';
                $col_arrow['status'] = '&#8595;';
                $order_field = 'b.status asc';
                break;
            
            case 'name-alt':
                $col_arrow['name'] = '&#8593;';
                $order_field = 'b.name desc';
                break;   
            case 'date-alt':
                 $col_arrow['date'] = '&#8593;';
                 $order_field = 'b.created_at desc';
                 break;   
            case 'surname-alt':
                $col_arrow['surname'] = '&#8593;';
                $order_field = 'b.surname desc';
                break; 
            case 'status-alt':
                $col_arrow['status'] = '&#8593;';
                $order_field = 'b.status desc';
                break; 
            case 'email-alt':
            default:
                $col_arrow['email'] = '&#8593;';
                $order_field = 'b.email desc';
                break;             
                
        }
        $view->orderalt = $alt_list;
        $view->orderby = $orderby;
        $view->col_arrow = $col_arrow;
        return $order_field;
    }
}