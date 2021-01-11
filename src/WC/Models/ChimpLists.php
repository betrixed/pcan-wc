<?php

namespace WC\Models;
use \Phiz\Mvc\ModelInterface;

class ChimpLists extends \Phiz\Mvc\Model
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
    protected $listid;

    /**
     *
     * @var string
     */
    protected $listName;

    /**
     *
     * @var integer
     */
    protected $subscribed;

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
     *
     * @var string
     */
    protected $last_send;

    /**
     *
     * @var string
     */
    protected $last_sub;

    /**
     *
     * @var string
     */
    protected $last_unsub;

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
     * Method to set the value of field listid
     *
     * @param string $listid
     * @return $this
     */
    public function setListid($listid)
    {
        $this->listid = $listid;

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
     * Method to set the value of field subscribed
     *
     * @param integer $subscribed
     * @return $this
     */
    public function setSubscribed($subscribed)
    {
        $this->subscribed = $subscribed;

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
     * Method to set the value of field last_send
     *
     * @param string $last_send
     * @return $this
     */
    public function setLastSend($last_send)
    {
        $this->last_send = $last_send;

        return $this;
    }

    /**
     * Method to set the value of field last_sub
     *
     * @param string $last_sub
     * @return $this
     */
    public function setLastSub($last_sub)
    {
        $this->last_sub = $last_sub;

        return $this;
    }

    /**
     * Method to set the value of field last_unsub
     *
     * @param string $last_unsub
     * @return $this
     */
    public function setLastUnsub($last_unsub)
    {
        $this->last_unsub = $last_unsub;

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
     * Returns the value of field listid
     *
     * @return string
     */
    public function getListid()
    {
        return $this->listid;
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
     * Returns the value of field subscribed
     *
     * @return integer
     */
    public function getSubscribed()
    {
        return $this->subscribed;
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
     * Returns the value of field last_send
     *
     * @return string
     */
    public function getLastSend()
    {
        return $this->last_send;
    }

    /**
     * Returns the value of field last_sub
     *
     * @return string
     */
    public function getLastSub()
    {
        return $this->last_sub;
    }

    /**
     * Returns the value of field last_unsub
     *
     * @return string
     */
    public function getLastUnsub()
    {
        return $this->last_unsub;
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
        // 
        $this->setSource("chimp_lists");
        $this->hasMany('id', 'WC\Models\ChimpEntry', 'listid', ['alias' => 'ChimpEntry']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ChimpLists[]|ChimpLists|\Phiz\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phiz\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ChimpLists|\Phiz\Mvc\Model\ResultInterface
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
            'listid' => 'listid',
            'listName' => 'listName',
            'subscribed' => 'subscribed',
            'unsubscribed' => 'unsubscribed',
            'cleaned' => 'cleaned',
            'last_send' => 'last_send',
            'last_sub' => 'last_sub',
            'last_unsub' => 'last_unsub',
            'phpjson' => 'phpjson'
        ];
    }

}
