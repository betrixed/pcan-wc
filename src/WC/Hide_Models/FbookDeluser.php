<?php

namespace WC\Models;

class FbookDeluser extends \Phalcon\Mvc\Model
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
    protected $fb_user;

    /**
     *
     * @var string
     */
    protected $created_at;

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
     * Method to set the value of field fb_user
     *
     * @param integer $fb_user
     * @return $this
     */
    public function setFbUser($fb_user)
    {
        $this->fb_user = $fb_user;

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
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field fb_user
     *
     * @return integer
     */
    public function getFbUser()
    {
        return $this->fb_user;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("fbook_deluser");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FbookDeluser[]|FbookDeluser|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FbookDeluser|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null) : \Phalcon\Mvc\ModelInterface
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
            'fb_user' => 'fb_user',
            'created_at' => 'created_at'
        ];
    }

}
