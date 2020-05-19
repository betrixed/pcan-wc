<?php

namespace App\Models;

class Mclist extends \Phalcon\Mvc\Model
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
    protected $listId;

    /**
     *
     * @var string
     */
    protected $listName;

    /**
     *
     * @var integer
     */
    protected $members;

    /**
     *
     * @var integer
     */
    protected $unsubscribed;

    /**
     *
     * @var integer
     */
    protected $cleaned;

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
     * Method to set the value of field listName
     *
     * @param string $listName
     * @return $this
     */
    public function setListName($listName)
    {
        $this->listName = $listName;

        return $this;
    }

    /**
     * Method to set the value of field members
     *
     * @param integer $members
     * @return $this
     */
    public function setMembers($members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * Method to set the value of field unsubscribed
     *
     * @param integer $unsubscribed
     * @return $this
     */
    public function setUnsubscribed($unsubscribed)
    {
        $this->unsubscribed = $unsubscribed;

        return $this;
    }

    /**
     * Method to set the value of field cleaned
     *
     * @param integer $cleaned
     * @return $this
     */
    public function setCleaned($cleaned)
    {
        $this->cleaned = $cleaned;

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
     * Returns the value of field listId
     *
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Returns the value of field listName
     *
     * @return string
     */
    public function getListName()
    {
        return $this->listName;
    }

    /**
     * Returns the value of field members
     *
     * @return integer
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Returns the value of field unsubscribed
     *
     * @return integer
     */
    public function getUnsubscribed()
    {
        return $this->unsubscribed;
    }

    /**
     * Returns the value of field cleaned
     *
     * @return integer
     */
    public function getCleaned()
    {
        return $this->cleaned;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pcan");
        $this->setSource("mclist");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Mclist[]|Mclist|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Mclist|\Phalcon\Mvc\Model\ResultInterface
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
            'listId' => 'listId',
            'listName' => 'listName',
            'members' => 'members',
            'unsubscribed' => 'unsubscribed',
            'cleaned' => 'cleaned'
        ];
    }

}
