<?php

namespace App\Models;

class ChimpEntry extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $listid;

    /**
     *
     * @var string
     */
    protected $uniqueid;

    /**
     *
     * @var string
     */
    protected $chimpid;

    /**
     *
     * @var integer
     */
    protected $emailid;

    /**
     *
     * @var string
     */
    protected $status;

    /**
     * Method to set the value of field listid
     *
     * @param integer $listid
     * @return $this
     */
    public function setListid($listid)
    {
        $this->listid = $listid;

        return $this;
    }

    /**
     * Method to set the value of field uniqueid
     *
     * @param string $uniqueid
     * @return $this
     */
    public function setUniqueid($uniqueid)
    {
        $this->uniqueid = $uniqueid;

        return $this;
    }

    /**
     * Method to set the value of field chimpid
     *
     * @param string $chimpid
     * @return $this
     */
    public function setChimpid($chimpid)
    {
        $this->chimpid = $chimpid;

        return $this;
    }

    /**
     * Method to set the value of field emailid
     *
     * @param integer $emailid
     * @return $this
     */
    public function setEmailid($emailid)
    {
        $this->emailid = $emailid;

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
     * Returns the value of field listid
     *
     * @return integer
     */
    public function getListid()
    {
        return $this->listid;
    }

    /**
     * Returns the value of field uniqueid
     *
     * @return string
     */
    public function getUniqueid()
    {
        return $this->uniqueid;
    }

    /**
     * Returns the value of field chimpid
     *
     * @return string
     */
    public function getChimpid()
    {
        return $this->chimpid;
    }

    /**
     * Returns the value of field emailid
     *
     * @return integer
     */
    public function getEmailid()
    {
        return $this->emailid;
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
        // $this->setSchema("pcan");
        $this->setSource("chimp_entry");
        $this->belongsTo('listid', 'App\Models\ChimpLists', 'id', ['alias' => 'ChimpLists']);
        $this->belongsTo('emailid', 'App\Models\MemberEmail', 'id', ['alias' => 'MemberEmail']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ChimpEntry[]|ChimpEntry|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ChimpEntry|\Phalcon\Mvc\Model\ResultInterface
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
            'listid' => 'listid',
            'uniqueid' => 'uniqueid',
            'chimpid' => 'chimpid',
            'emailid' => 'emailid',
            'status' => 'status'
        ];
    }

}
