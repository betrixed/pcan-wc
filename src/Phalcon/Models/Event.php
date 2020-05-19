<?php

namespace App\Models;

class Event extends \Phalcon\Mvc\Model
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
    protected $fromtime;

    /**
     *
     * @var string
     */
    protected $totime;

    /**
     *
     * @var integer
     */
    protected $blogid;

    /**
     *
     * @var string
     */
    protected $enabled;

    /**
     *
     * @var string
     */
    protected $slug;

    /**
     *
     * @var string
     */
    protected $reg_detail;

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
     * Method to set the value of field fromtime
     *
     * @param string $fromtime
     * @return $this
     */
    public function setFromtime($fromtime)
    {
        $this->fromtime = $fromtime;

        return $this;
    }

    /**
     * Method to set the value of field totime
     *
     * @param string $totime
     * @return $this
     */
    public function setTotime($totime)
    {
        $this->totime = $totime;

        return $this;
    }

    /**
     * Method to set the value of field blogid
     *
     * @param integer $blogid
     * @return $this
     */
    public function setBlogid($blogid)
    {
        $this->blogid = $blogid;

        return $this;
    }

    /**
     * Method to set the value of field enabled
     *
     * @param string $enabled
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Method to set the value of field slug
     *
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Method to set the value of field reg_detail
     *
     * @param string $reg_detail
     * @return $this
     */
    public function setRegDetail($reg_detail)
    {
        $this->reg_detail = $reg_detail;

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
     * Returns the value of field fromtime
     *
     * @return string
     */
    public function getFromtime()
    {
        return $this->fromtime;
    }

    /**
     * Returns the value of field totime
     *
     * @return string
     */
    public function getTotime()
    {
        return $this->totime;
    }

    /**
     * Returns the value of field blogid
     *
     * @return integer
     */
    public function getBlogid()
    {
        return $this->blogid;
    }

    /**
     * Returns the value of field enabled
     *
     * @return string
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Returns the value of field slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Returns the value of field reg_detail
     *
     * @return string
     */
    public function getRegDetail()
    {
        return $this->reg_detail;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pcan");
        $this->setSource("event");
        $this->belongsTo('blogid', 'App\Models\Blog', 'id', ['alias' => 'Blog']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Event[]|Event|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Event|\Phalcon\Mvc\Model\ResultInterface
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
            'fromtime' => 'fromtime',
            'totime' => 'totime',
            'blogid' => 'blogid',
            'enabled' => 'enabled',
            'slug' => 'slug',
            'reg_detail' => 'reg_detail',
        ];
    }

}
