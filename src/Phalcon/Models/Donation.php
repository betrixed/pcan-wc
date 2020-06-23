<?php

namespace App\Models;

class Donation extends \Phalcon\Mvc\Model
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
     * @var double
     */
    protected $amount;

    /**
     *
     * @var string
     */
    protected $purpose;

    /**
     *
     * @var string
     */
    protected $created_at;

    /**
     *
     * @var string
     */
    protected $member_date;

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
     * Method to set the value of field amount
     *
     * @param double $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Method to set the value of field purpose
     *
     * @param string $purpose
     * @return $this
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;

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
     * Method to set the value of field member_date
     *
     * @param string $member_date
     * @return $this
     */
    public function setMemberDate($member_date)
    {
        $this->member_date = $member_date;

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
     * Returns the value of field amount
     *
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Returns the value of field purpose
     *
     * @return string
     */
    public function getPurpose()
    {
        return $this->purpose;
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
     * Returns the value of field member_date
     *
     * @return string
     */
    public function getMemberDate()
    {
        return $this->member_date;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // $this->setSchema("pcan");
        $this->setSource("donation");
        $this->belongsTo('memberid', 'App\Models\Member', 'id', ['alias' => 'Member']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Donation[]|Donation|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Donation|\Phalcon\Mvc\Model\ResultInterface
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
            'amount' => 'amount',
            'purpose' => 'purpose',
            'created_at' => 'created_at',
            'member_date' => 'member_date'
        ];
    }

}
