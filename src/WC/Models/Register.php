<?php

namespace WC\Models;
use \Phiz\Mvc\ModelInterface;

use Phiz\Validation;
use Phiz\Validation\Validator\Email as EmailValidator;

class Register extends \Phiz\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var integer
     */
    protected $eventid;

    /**
     *
     * @var string
     */
    protected $created_at;

    /**
     *
     * @var string
     */
    protected $notkeep;

    /**
     *
     * @var string
     */
    protected $people;

    /**
     *
     * @var string
     */
    protected $fname;

    /**
     *
     * @var string
     */
    protected $lname;

    /**
     *
     * @var string
     */
    protected $email;

    /**
     *
     * @var string
     */
    protected $phone;

    /**
     *
     * @var string
     */
    protected $linkcode;

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
     * Method to set the value of field eventid
     *
     * @param integer $eventid
     * @return $this
     */
    public function setEventid($eventid)
    {
        $this->eventid = $eventid;

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
     * Method to set the value of field notkeep
     *
     * @param string $notkeep
     * @return $this
     */
    public function setNotkeep($notkeep)
    {
        $this->notkeep = $notkeep;

        return $this;
    }

    /**
     * Method to set the value of field people
     *
     * @param string $people
     * @return $this
     */
    public function setPeople($people)
    {
        $this->people = $people;

        return $this;
    }

    /**
     * Method to set the value of field fname
     *
     * @param string $fname
     * @return $this
     */
    public function setFname($fname)
    {
        $this->fname = $fname;

        return $this;
    }

    /**
     * Method to set the value of field lname
     *
     * @param string $lname
     * @return $this
     */
    public function setLname($lname)
    {
        $this->lname = $lname;

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
     * Method to set the value of field phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Method to set the value of field linkcode
     *
     * @param string $linkcode
     * @return $this
     */
    public function setLinkcode($linkcode)
    {
        $this->linkcode = $linkcode;

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
     * Returns the value of field eventid
     *
     * @return integer
     */
    public function getEventid()
    {
        return $this->eventid;
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
     * Returns the value of field notkeep
     *
     * @return string
     */
    public function getNotkeep()
    {
        return $this->notkeep;
    }

    /**
     * Returns the value of field people
     *
     * @return string
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * Returns the value of field fname
     *
     * @return string
     */
    public function getFname()
    {
        return $this->fname;
    }

    /**
     * Returns the value of field lname
     *
     * @return string
     */
    public function getLname()
    {
        return $this->lname;
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
     * Returns the value of field phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Returns the value of field linkcode
     *
     * @return string
     */
    public function getLinkcode()
    {
        return $this->linkcode;
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
        $this->setSource("register");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Register[]|Register|\Phiz\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phiz\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Register|\Phiz\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null): ?ModelInterface
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
            'eventid' => 'eventid',
            'created_at' => 'created_at',
            'notkeep' => 'notkeep',
            'people' => 'people',
            'fname' => 'fname',
            'lname' => 'lname',
            'email' => 'email',
            'phone' => 'phone',
            'linkcode' => 'linkcode'
        ];
    }

}
