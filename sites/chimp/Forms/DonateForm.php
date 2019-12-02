<?php

/*
See the "licence.txt" file at the root "private" folder of this site
*/

namespace Chimp\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Check;

use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Digit;

class DonateForm extends Form 
{
    
    public function initialize($entity = null, $options = null)
    {  
        $id = new Text('purpose', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Purpose');
        $this->add($id);
        
        $id = new Text('amount', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Amount');
        $id->addValidator( new Digit ([ "message" => "Must be currency"]));
        $this->add($id);
        
        
        $id = new Date('member_date');
        $id->setLabel('Date');
        $this->add($id);
        
        $id = new Hidden('mcid');
        $this->add($id);  
        
        
    }
}