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


class MemberForm extends Form 
{
    public function initialize($entity = null, $options = null)
    {  
        $id = new Text('name', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Name');
        $this->add($id);
        
        $id = new Text('surname', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Surname');
        $this->add($id);  
        
        $id = new Text('info', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Info');
        $this->add($id);
        
        $id = new Text('phone1', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Phone-1');
        $this->add($id);

        $id = new Text('phone2', array('size' => 20, 'maxlength'=>60));
        $id->setLabel('Phone-2');
        $this->add($id);

        $id = new Text('email', array('size' => 40, 'maxlength'=>80));
        $id->setLabel('Email');
        $this->add($id);
        
        $id = new Select('status', array(
            'no-chimp' => 'No Mailchimp',
            'no-email' => 'No Email',
            'subscribed' => 'Subscribed',
            'unsubscribed' => 'Unsubscribed',
            'cleaned' => 'Cleaned',
            'pending' => 'Pending'
                )
                );
        
        $id->setLabel('Mail Chimp Status');
        $this->add($id);  
        
        $id = new Hidden('mcid'); // md5 hash identity, of lower case version of email address
        $this->add($id);  
        
        // more optional fields
        $id = new Select('memberType', array(
            'elist' => 'E-List',
            'member' => 'Member',
            'subscriber' => 'Subscriber',
            'supporter' => 'Supporter',
            'work-group' => 'Working Group'
                )
                );
        $id->setLabel('Member Type');
        $this->add($id);
        
        $id = new Text('financial', [ 'size' => 16, 'maxlength' => 16]);
        $id->setLabel('Financial');
        $this->add($id);
        
        $id = new TextArea('interests', [ 'rows' => 5, 'cols' => 80, 'maxlength' => 255]);
        $id->setLabel('Interests');
        $this->add($id);
        
        $id = new Text('statustype', [ 'size' => 40, 'maxlength' => 255]);
        $id->setLabel('Status Type');
        $this->add($id);
        
        $id = new Check('volunteer');
        $id->setLabel('Volunteer');
        $this->add($id);
        
        $id = new Text('position', [ 'size' => 40, 'maxlength' => 255]);
        $id->setLabel('Position');
        $this->add($id);
        
        $id = new Text('organisation', [ 'size' => 40, 'maxlength' => 255]);
        $id->setLabel('Organisation');
        $this->add($id);
        
        $id = new Text('address1', [ 'size' => 40, 'maxlength' => 80]);
        $id->setLabel('Address - # Street');
        $this->add($id);
        
        $id = new Text('suburb', [ 'size' => 40, 'maxlength' => 50]);
        $id->setLabel('Suburb / City');
        $this->add($id);
        
        $id = new Text('state', [ 'size' => 16, 'maxlength' => 20]);
        $id->setLabel('State');
        $this->add($id);
        
        $id = new Text('postcode', [ 'size' => 7, 'maxlength' => 7]);
        $id->setLabel('Post-code');
        $this->add($id);
        
        
    }    
}
