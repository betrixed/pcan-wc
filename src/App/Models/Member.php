<?php

namespace App\Models;

class Member extends \Phalcon\Mvc\Model
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
    protected $addr1;

    /**
     *
     * @var string
     */
    protected $addr2;

    /**
     *
     * @var string
     */
    protected $city;

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
     *
     * @var string
     */
    protected $country_code;

    /**
     *
     * @var string
     */
    protected $phone;

    /**
     *
     * @var string
     */
    protected $ref_source;

    /**
     *
     * @var string
     */
    protected $create_date;

    /**
     *
     * @var string
     */
    protected $last_update;

    /**
     *
     * @var string
     */
    protected $status;

    /**
     *
     * @var string
     */
    protected $phpjson;

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
     * Method to set the value of field addr1
     *
     * @param string $addr1
     * @return $this
     */
    public function setAddr1($addr1)
    {
        $this->addr1 = $addr1;

        return $this;
    }

    /**
     * Method to set the value of field addr2
     *
     * @param string $addr2
     * @return $this
     */
    public function setAddr2($addr2)
    {
        $this->addr2 = $addr2;

        return $this;
    }

    /**
     * Method to set the value of field city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

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
     * Method to set the value of field country_code
     *
     * @param string $country_code
     * @return $this
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;

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
     * Method to set the value of field source
     *
     * @param string $source
     * @return $this
     */
    public function setRefSource($source)
    {
        $this->ref_source = $source;

        return $this;
    }

    /**
     * Method to set the value of field create_date
     *
     * @param string $create_date
     * @return $this
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * Method to set the value of field last_update
     *
     * @param string $last_update
     * @return $this
     */
    public function setLastUpdate($last_update)
    {
        $this->last_update = $last_update;

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
     * Method to set the value of field phpjson
     *
     * @param string $phpjson
     * @return $this
     */
    public function setPhpjson($phpjson)
    {
        $this->phpjson = $phpjson;

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
     * Returns the value of field addr1
     *
     * @return string
     */
    public function getAddr1()
    {
        return $this->addr1;
    }

    /**
     * Returns the value of field addr2
     *
     * @return string
     */
    public function getAddr2()
    {
        return $this->addr2;
    }

    /**
     * Returns the value of field city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
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
     * Returns the value of field country_code
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
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
     * Returns the value of field source
     *
     * @return string
     */
    public function getRefSource()
    {
        return $this->ref_source;
    }

    /**
     * Returns the value of field create_date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Returns the value of field last_update
     *
     * @return string
     */
    public function getLastUpdate()
    {
        return $this->last_update;
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
     * Returns the value of field phpjson
     *
     * @return string
     */
    public function getPhpjson()
    {
        return $this->phpjson;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // $this->setSchema("pcan");
        $this->setSource("member");
        $this->hasMany('id', 'App\Models\Donation', 'memberid', ['alias' => 'Donation']);
        $this->hasMany('id', 'App\Models\MemberEmail', 'memberid', ['alias' => 'MemberEmail']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Member[]|Member|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Member|\Phalcon\Mvc\Model\ResultInterface
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
            'fname' => 'fname',
            'lname' => 'lname',
            'addr1' => 'addr1',
            'addr2' => 'addr2',
            'city' => 'city',
            'state' => 'state',
            'postcode' => 'postcode',
            'country_code' => 'country_code',
            'phone' => 'phone',
            'source' => 'ref_source',
            'create_date' => 'create_date',
            'last_update' => 'last_update',
            'status' => 'status',
            'phpjson' => 'phpjson'
        ];
    }

}
