<?php

namespace WC\Models;
use \Phalcon\Mvc\ModelInterface;

class RssFeed extends \Phalcon\Mvc\Model
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
    protected $last_read;

    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var string
     */
    protected $nick_name;

    /**
     *
     * @var string
     */
    protected $provider;

    /**
     *
     * @var string
     */
    protected $use_https;

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
     * Method to set the value of field last_read
     *
     * @param string $last_read
     * @return $this
     */
    public function setLastRead($last_read)
    {
        $this->last_read = $last_read;

        return $this;
    }

    /**
     * Method to set the value of field content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Method to set the value of field nick_name
     *
     * @param string $nick_name
     * @return $this
     */
    public function setNickName($nick_name)
    {
        $this->nick_name = $nick_name;

        return $this;
    }

    /**
     * Method to set the value of field provider
     *
     * @param string $provider
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Method to set the value of field use_https
     *
     * @param string $use_https
     * @return $this
     */
    public function setUseHttps($use_https)
    {
        $this->use_https = $use_https;

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
     * Returns the value of field last_read
     *
     * @return string
     */
    public function getLastRead()
    {
        return $this->last_read;
    }

    /**
     * Returns the value of field content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Returns the value of field nick_name
     *
     * @return string
     */
    public function getNickName()
    {
        return $this->nick_name;
    }

    /**
     * Returns the value of field provider
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Returns the value of field use_https
     *
     * @return string
     */
    public function getUseHttps()
    {
        return $this->use_https;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("rss_feed");
        $this->hasMany('id', 'WC\Models\RssLink', 'feed_id', ['alias' => 'RssLink']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return RssFeed[]|RssFeed|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return RssFeed|\Phalcon\Mvc\Model\ResultInterface
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
            'url' => 'url',
            'last_read' => 'last_read',
            'content' => 'content',
            'nick_name' => 'nick_name',
            'provider' => 'provider',
            'use_https' => 'use_https'
        ];
    }

}
