<?php

namespace WC\Models;
use \Phiz\Mvc\ModelInterface;

class UserEvent extends \Phiz\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $user_id;

    /**
     *
     * @var string
     */
    protected $created_at;

    /**
     *
     * @var string
     */
    protected $event_type;

    /**
     *
     * @var string
     */
    protected $data;

    /**
     *
     * @var string
     */
    protected $status_ip;

    /**
     * Method to set the value of field user_id
     *
     * @param integer $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

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
     * Method to set the value of field event_type
     *
     * @param string $event_type
     * @return $this
     */
    public function setEventType($event_type)
    {
        $this->event_type = $event_type;

        return $this;
    }

    /**
     * Method to set the value of field data
     *
     * @param string $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Method to set the value of field status_ip
     *
     * @param string $status_ip
     * @return $this
     */
    public function setStatusIp($status_ip)
    {
        $this->status_ip = $status_ip;

        return $this;
    }

    /**
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
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
     * Returns the value of field event_type
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * Returns the value of field data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the value of field status_ip
     *
     * @return string
     */
    public function getStatusIp()
    {
        return $this->status_ip;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("user_event");
        $this->belongsTo('user_id', 'WC\Models\Users', 'id', ['alias' => 'Users']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserEvent[]|UserEvent|\Phiz\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phiz\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserEvent|\Phiz\Mvc\Model\ResultInterface
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
            'user_id' => 'user_id',
            'created_at' => 'created_at',
            'event_type' => 'event_type',
            'data' => 'data',
            'status_ip' => 'status_ip'
        ];
    }

}
