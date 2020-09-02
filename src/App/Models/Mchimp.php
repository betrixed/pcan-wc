<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

class Mchimp extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $email;

    /**
     *
     * @var string
     */
    protected $mcid;

    /**
     *
     * @var string
     */
    protected $listId;

    /**
     *
     * @var string
     */
    protected $phone1;

    /**
     *
     * @var string
     */
    protected $phone2;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $surname;

    /**
     *
     * @var string
     */
    protected $info;

    /**
     *
     * @var string
     */
    protected $status;

    /**
     *
     * @var string
     */
    protected $created_at;

    /**
     *
     * @var string
     */
    protected $changed_at;

    /**
     *
     * @var string
     */
    protected $memberType;

    /**
     *
     * @var string
     */
    protected $financial;

    /**
     *
     * @var string
     */
    protected $interests;

    /**
     *
     * @var string
     */
    protected $volunteer;

    /**
     *
     * @var string
     */
    protected $position;

    /**
     *
     * @var string
     */
    protected $organisation;

    /**
     *
     * @var string
     */
    protected $address1;

    /**
     *
     * @var string
     */
    protected $suburb;

    /**
     *
     * @var string
     */
    protected $state;

    /**
     *
     * @var string
     */
    protected $postcode;

    /**
     * Method to set the value of field id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to set the value of field email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Method to set the value of field mcid
     *
     * @param string $mcid
     * @return $this
     */
    public function setMcid($mcid)
    {
        $this->mcid = $mcid;

        return $this;
    }

    /**
     * Method to set the value of field listId
     *
     * @param string $listId
     * @return $this
     */
    public function setListId($listId)
    {
        $this->listId = $listId;

        return $this;
    }

    /**
     * Method to set the value of field phone1
     *
     * @param string $phone1
     * @return $this
     */
    public function setPhone1($phone1)
    {
        $this->phone1 = $phone1;

        return $this;
    }

    /**
     * Method to set the value of field phone2
     *
     * @param string $phone2
     * @return $this
     */
    public function setPhone2($phone2)
    {
        $this->phone2 = $phone2;

        return $this;
    }

    /**
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field surname
     *
     * @param string $surname
     * @return $this
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Method to set the value of field info
     *
     * @param string $info
     * @return $this
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Method to set the value of field status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Method to set the value of field created_at
     *
     * @param string $created_at
     * @return $this
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Method to set the value of field changed_at
     *
     * @param string $changed_at
     * @return $this
     */
    public function setChangedAt($changed_at)
    {
        $this->changed_at = $changed_at;

        return $this;
    }

    /**
     * Method to set the value of field memberType
     *
     * @param string $memberType
     * @return $this
     */
    public function setMemberType($memberType)
    {
        $this->memberType = $memberType;

        return $this;
    }

    /**
     * Method to set the value of field financial
     *
     * @param string $financial
     * @return $this
     */
    public function setFinancial($financial)
    {
        $this->financial = $financial;

        return $this;
    }

    /**
     * Method to set the value of field interests
     *
     * @param string $interests
     * @return $this
     */
    public function setInterests($interests)
    {
        $this->interests = $interests;

        return $this;
    }

    /**
     * Method to set the value of field volunteer
     *
     * @param string $volunteer
     * @return $this
     */
    public function setVolunteer($volunteer)
    {
        $this->volunteer = $volunteer;

        return $this;
    }

    /**
     * Method to set the value of field position
     *
     * @param string $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Method to set the value of field organisation
     *
     * @param string $organisation
     * @return $this
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Method to set the value of field address1
     *
     * @param string $address1
     * @return $this
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Method to set the value of field suburb
     *
     * @param string $suburb
     * @return $this
     */
    public function setSuburb($suburb)
    {
        $this->suburb = $suburb;

        return $this;
    }

    /**
     * Method to set the value of field state
     *
     * @param string $state
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Method to set the value of field postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the value of field mcid
     *
     * @return string
     */
    public function getMcid()
    {
        return $this->mcid;
    }

    /**
     * Returns the value of field listId
     *
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Returns the value of field phone1
     *
     * @return string
     */
    public function getPhone1()
    {
        return $this->phone1;
    }

    /**
     * Returns the value of field phone2
     *
     * @return string
     */
    public function getPhone2()
    {
        return $this->phone2;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Returns the value of field info
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Returns the value of field status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the value of field created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Returns the value of field changed_at
     *
     * @return string
     */
    public function getChangedAt()
    {
        return $this->changed_at;
    }

    /**
     * Returns the value of field memberType
     *
     * @return string
     */
    public function getMemberType()
    {
        return $this->memberType;
    }

    /**
     * Returns the value of field financial
     *
     * @return string
     */
    public function getFinancial()
    {
        return $this->financial;
    }

    /**
     * Returns the value of field interests
     *
     * @return string
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * Returns the value of field volunteer
     *
     * @return string
     */
    public function getVolunteer()
    {
        return $this->volunteer;
    }

    /**
     * Returns the value of field position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Returns the value of field organisation
     *
     * @return string
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Returns the value of field address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Returns the value of field suburb
     *
     * @return string
     */
    public function getSuburb()
    {
        return $this->suburb;
    }

    /**
     * Returns the value of field state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Returns the value of field postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model'   => $this,
                    'message' => 'Please enter a correct email address',
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("mchimp");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Mchimp[]|Mchimp|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Mchimp|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return [
            'id' => 'id',
            'email' => 'email',
            'mcid' => 'mcid',
            'listId' => 'listId',
            'phone1' => 'phone1',
            'phone2' => 'phone2',
            'name' => 'name',
            'surname' => 'surname',
            'info' => 'info',
            'status' => 'status',
            'created_at' => 'created_at',
            'changed_at' => 'changed_at',
            'memberType' => 'memberType',
            'financial' => 'financial',
            'interests' => 'interests',
            'volunteer' => 'volunteer',
            'position' => 'position',
            'organisation' => 'organisation',
            'address1' => 'address1',
            'suburb' => 'suburb',
            'state' => 'state',
            'postcode' => 'postcode'
        ];
    }

}
