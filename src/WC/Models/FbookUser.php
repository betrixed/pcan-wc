<?php

namespace WC\Models;

class FbookUser extends \Phalcon\Mvc\Model
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
    protected $userid;

    /**
     *
     * @var string
     */
    protected $fb_email;

    /**
     *
     * @var string
     */
    protected $fb_name;

    /**
     *
     * @var string
     */
    protected $created_at;

    /**
     *
     * @var string
     */
    protected $modified_at;

    /**
     *
     * @var integer
     */
    protected $update_count;

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
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Method to set the value of field fb_email
     *
     * @param string $fb_email
     * @return $this
     */
    public function setFbEmail($fb_email)
    {
        $this->fb_email = $fb_email;

        return $this;
    }

    /**
     * Method to set the value of field fb_name
     *
     * @param string $fb_name
     * @return $this
     */
    public function setFbName($fb_name)
    {
        $this->fb_name = $fb_name;

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
     * Method to set the value of field modified_at
     *
     * @param string $modified_at
     * @return $this
     */
    public function setModifiedAt($modified_at)
    {
        $this->modified_at = $modified_at;

        return $this;
    }

    /**
     * Method to set the value of field update_count
     *
     * @param integer $update_count
     * @return $this
     */
    public function setUpdateCount($update_count)
    {
        $this->update_count = $update_count;

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
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Returns the value of field fb_email
     *
     * @return string
     */
    public function getFbEmail()
    {
        return $this->fb_email;
    }

    /**
     * Returns the value of field fb_name
     *
     * @return string
     */
    public function getFbName()
    {
        return $this->fb_name;
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
     * Returns the value of field modified_at
     *
     * @return string
     */
    public function getModifiedAt()
    {
        return $this->modified_at;
    }

    /**
     * Returns the value of field update_count
     *
     * @return integer
     */
    public function getUpdateCount()
    {
        return $this->update_count;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("fbook_user");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FbookUser[]|FbookUser|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FbookUser|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
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
            'userid' => 'userid',
            'fb_email' => 'fb_email',
            'fb_name' => 'fb_name',
            'created_at' => 'created_at',
            'modified_at' => 'modified_at',
            'update_count' => 'update_count'
        ];
    }

}
