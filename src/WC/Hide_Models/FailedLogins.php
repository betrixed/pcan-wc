<?php

namespace WC\Models;
use \Phalcon\Mvc\ModelInterface;

class FailedLogins extends \Phalcon\Mvc\Model
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
    protected $usersId;

    /**
     *
     * @var string
     */
    protected $ipAddress;

    /**
     *
     * @var string
     */
    protected $attempted;

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
     * Method to set the value of field usersId
     *
     * @param integer $usersId
     * @return $this
     */
    public function setUsersId($usersId)
    {
        $this->usersId = $usersId;

        return $this;
    }

    /**
     * Method to set the value of field ipAddress
     *
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Method to set the value of field attempted
     *
     * @param string $attempted
     * @return $this
     */
    public function setAttempted($attempted)
    {
        $this->attempted = $attempted;

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
     * Returns the value of field usersId
     *
     * @return integer
     */
    public function getUsersId()
    {
        return $this->usersId;
    }

    /**
     * Returns the value of field ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Returns the value of field attempted
     *
     * @return string
     */
    public function getAttempted()
    {
        return $this->attempted;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("failed_logins");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FailedLogins[]|FailedLogins|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FailedLogins|\Phalcon\Mvc\Model\ResultInterface
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
            'usersId' => 'usersId',
            'ipAddress' => 'ipAddress',
            'attempted' => 'attempted'
        ];
    }

}
