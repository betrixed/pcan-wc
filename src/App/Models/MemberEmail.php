<?php

namespace App\Models;

class MemberEmail extends \Phalcon\Mvc\Model
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
    protected $memberid;

    /**
     *
     * @var string
     */
    protected $email_address;

    /**
     *
     * @var string
     */
    protected $status;

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
     * Method to set the value of field memberid
     *
     * @param integer $memberid
     * @return $this
     */
    public function setMemberid($memberid)
    {
        $this->memberid = $memberid;

        return $this;
    }

    /**
     * Method to set the value of field email_address
     *
     * @param string $email_address
     * @return $this
     */
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;

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
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field memberid
     *
     * @return integer
     */
    public function getMemberid()
    {
        return $this->memberid;
    }

    /**
     * Returns the value of field email_address
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->email_address;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("member_email");
        $this->hasMany('id', 'App\Models\ChimpEntry', 'emailid', ['alias' => 'ChimpEntry']);
        $this->belongsTo('memberid', 'App\Models\Member', 'id', ['alias' => 'Member']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MemberEmail[]|MemberEmail|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MemberEmail|\Phalcon\Mvc\Model\ResultInterface
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
            'memberid' => 'memberid',
            'email_address' => 'email_address',
            'status' => 'status'
        ];
    }

}
