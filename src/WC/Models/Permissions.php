<?php

namespace WC\Models;
use \Phiz\Mvc\ModelInterface;

class Permissions extends \Phiz\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $groupId;

    /**
     *
     * @var integer
     */
    protected $resourceId;

    /**
     * Method to set the value of field groupId
     *
     * @param integer $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Method to set the value of field resourceId
     *
     * @param integer $resourceId
     * @return $this
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * Returns the value of field groupId
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Returns the value of field resourceId
     *
     * @return integer
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("permissions");
        $this->belongsTo('groupId', 'WC\Models\UserGroup', 'id', ['alias' => 'UserGroup']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Permissions[]|Permissions|\Phiz\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phiz\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Permissions|\Phiz\Mvc\Model\ResultInterface
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
            'groupId' => 'groupId',
            'resourceId' => 'resourceId'
        ];
    }

}
