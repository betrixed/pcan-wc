<?php

/*
See the "licence.txt" file at the root "private" folder of this site
*/

namespace Chimp\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Check;

use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email;

class McqueryForm extends Form 
{
    
    public function initialize($entity = null, $options = null)
    {  
        $id = new Text('name', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Name');
        $this->add($id);
        
        $id = new Select('name_sel', array(
            'contain' => 'Contains',
            'start' => 'Starts with',
            'match' => 'Matches'
        ));
        $id->setLabel('name_sel');
        $this->add($id);
        
        
        $id = new Select('hasPhone', array(
            '' => 'N/A',
            'no' => 'No',
            'yes' => 'Yes'   
        ));
        
        $id->setLabel('Has Phone');
        $this->add($id);
        
        $id = new Text('surname', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Surname');
        $this->add($id);

        $id = new Select('surname_sel', array(
            'contain' => 'Contains',
            'start' => 'Starts with',
            'match' => 'Matches'
        ));
        $id->setLabel('surname_sel');
        $this->add($id);
        
        $id = new Select('statustype', array(
            '' => 'Any',
            'subscribed' => 'Subscribed',
            'unsubscribed' => 'Unsubscribed',
            'cleaned' => 'Cleaned',
            'pending' => 'Pending',
            'no-email' => 'No Email'
                )
                );
        $id->setLabel('Subscriber Type');
        $this->add($id);  
        
        $id = new Select("membertype",
                [ '' => 'Any',
                  'current' => 'Current Member',
                  'past' => 'Past Member',
                  'none' => 'Never a Member',
                  'sponsor' => 'Sponsor'
                ]);
        $id->setLabel('Member Type');
        $this->add($id);  
        
        $id = new Hidden('orderby');
        $this->add($id);  
        
        
    }
}