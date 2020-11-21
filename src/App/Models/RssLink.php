<?php

namespace App\Models;
use \Phalcon\Mvc\ModelInterface;

class RssLink extends \Phalcon\Mvc\Model
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
    protected $feed_id;

    /**
     *
     * @var string
     */
    protected $guid;

    /**
     *
     * @var string
     */
    protected $pub_date;

    /**
     *
     * @var string
     */
    protected $description;

    /**
     *
     * @var integer
     */
    protected $flags;

    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var string
     */
    protected $link;

    /**
     *
     * @var string
     */
    protected $creator;

    /**
     *
     * @var string
     */
    protected $extract;

    /**
     *
     * @var string
     */
    protected $section;

    /**
     *
     * @var string
     */
    protected $category;

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
     * Method to set the value of field feed_id
     *
     * @param integer $feed_id
     * @return $this
     */
    public function setFeedId($feed_id)
    {
        $this->feed_id = $feed_id;

        return $this;
    }

    /**
     * Method to set the value of field guid
     *
     * @param string $guid
     * @return $this
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * Method to set the value of field pub_date
     *
     * @param string $pub_date
     * @return $this
     */
    public function setPubDate($pub_date)
    {
        $this->pub_date = $pub_date;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field flags
     *
     * @param integer $flags
     * @return $this
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

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
     * Method to set the value of field link
     *
     * @param string $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Method to set the value of field creator
     *
     * @param string $creator
     * @return $this
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Method to set the value of field extract
     *
     * @param string $extract
     * @return $this
     */
    public function setExtract($extract)
    {
        $this->extract = $extract;

        return $this;
    }

    /**
     * Method to set the value of field section
     *
     * @param string $section
     * @return $this
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Method to set the value of field category
     *
     * @param string $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

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
     * Returns the value of field feed_id
     *
     * @return integer
     */
    public function getFeedId()
    {
        return $this->feed_id;
    }

    /**
     * Returns the value of field guid
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Returns the value of field pub_date
     *
     * @return string
     */
    public function getPubDate()
    {
        return $this->pub_date;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field flags
     *
     * @return integer
     */
    public function getFlags()
    {
        return $this->flags;
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
     * Returns the value of field link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Returns the value of field creator
     *
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Returns the value of field extract
     *
     * @return string
     */
    public function getExtract()
    {
        return $this->extract;
    }

    /**
     * Returns the value of field section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Returns the value of field category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("rss_link");
        $this->belongsTo('feed_id', 'App\Models\RssFeed', 'id', ['alias' => 'RssFeed']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return RssLink[]|RssLink|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return RssLink|\Phalcon\Mvc\Model\ResultInterface
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
            'feed_id' => 'feed_id',
            'guid' => 'guid',
            'pub_date' => 'pub_date',
            'description' => 'description',
            'flags' => 'flags',
            'title' => 'title',
            'link' => 'link',
            'creator' => 'creator',
            'extract' => 'extract',
            'section' => 'section',
            'category' => 'category'
        ];
    }

}
