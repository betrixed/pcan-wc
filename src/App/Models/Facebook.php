<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

class Facebook extends \Phalcon\Mvc\Model
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
    protected $userid;

    /**
     *
     * @var string
     */
    protected $email;

    /**
     *
     * @var string
     */
    protected $fbname;

    /**
     *
     * @var string
     */
    protected $date_created;

    /**
     *
     * @var string
     */
    protected $phpdata;

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
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

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
     * Method to set the value of field fbname
     *
     * @param string $fbname
     * @return $this
     */
    public function setFbname($fbname)
    {
        $this->fbname = $fbname;

        return $this;
    }

    /**
     * Method to set the value of field date_created
     *
     * @param string $date_created
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * Method to set the value of field phpdata
     *
     * @param string $phpdata
     * @return $this
     */
    public function setPhpdata($phpdata)
    {
        $this->phpdata = $phpdata;

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
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserid()
    {
        return $this->userid;
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
     * Returns the value of field fbname
     *
     * @return string
     */
    public function getFbname()
    {
        return $this->fbname;
    }

    /**
     * Returns the value of field date_created
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Returns the value of field phpdata
     *
     * @return string
     */
    public function getPhpdata()
    {
        return $this->phpdata;
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
        $this->setSource("facebook");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Facebook[]|Facebook|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Facebook|\Phalcon\Mvc\Model\ResultInterface
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
            'userid' => 'userid',
            'email' => 'email',
            'fbname' => 'fbname',
            'date_created' => 'date_created',
            'phpdata' => 'phpdata'
        ];
    }

}
