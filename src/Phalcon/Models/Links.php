<?php

namespace App\Models;

class Links extends \Phalcon\Mvc\Model
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
    protected $url;

    /**
     *
     * @var string
     */
    protected $summary;

    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var string
     */
    protected $sitename;

    /**
     *
     * @var string
     */
    protected $date_created;

    /**
     *
     * @var string
     */
    protected $enabled;

    /**
     *
     * @var string
     */
    protected $urltype;

    /**
     *
     * @var integer
     */
    protected $refid;

    /**
     *
     * @var integer
     */
    protected $imageid;

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
     * Method to set the value of field url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Method to set the value of field summary
     *
     * @param string $summary
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Method to set the value of field title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Method to set the value of field sitename
     *
     * @param string $sitename
     * @return $this
     */
    public function setSitename($sitename)
    {
        $this->sitename = $sitename;

        return $this;
    }

    /**
     * Method to set the value of field date_created
     *
     * @param string $date_created
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;

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
     * Method to set the value of field urltype
     *
     * @param string $urltype
     * @return $this
     */
    public function setUrltype($urltype)
    {
        $this->urltype = $urltype;

        return $this;
    }

    /**
     * Method to set the value of field refid
     *
     * @param integer $refid
     * @return $this
     */
    public function setRefid($refid)
    {
        $this->refid = $refid;

        return $this;
    }

    /**
     * Method to set the value of field imageid
     *
     * @param integer $imageid
     * @return $this
     */
    public function setImageid($imageid)
    {
        $this->imageid = $imageid;

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
     * Returns the value of field url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns the value of field summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Returns the value of field title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the value of field sitename
     *
     * @return string
     */
    public function getSitename()
    {
        return $this->sitename;
    }

    /**
     * Returns the value of field date_created
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->date_created;
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
     * Returns the value of field urltype
     *
     * @return string
     */
    public function getUrltype()
    {
        return $this->urltype;
    }

    /**
     * Returns the value of field refid
     *
     * @return integer
     */
    public function getRefid()
    {
        return $this->refid;
    }

    /**
     * Returns the value of field imageid
     *
     * @return integer
     */
    public function getImageid()
    {
        return $this->imageid;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pcan");
        $this->setSource("links");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Links[]|Links|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Links|\Phalcon\Mvc\Model\ResultInterface
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
            'url' => 'url',
            'summary' => 'summary',
            'title' => 'title',
            'sitename' => 'sitename',
            'date_created' => 'date_created',
            'enabled' => 'enabled',
            'urltype' => 'urltype',
            'refid' => 'refid',
            'imageid' => 'imageid'
        ];
    }
}
