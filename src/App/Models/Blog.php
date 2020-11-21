<?php

namespace App\Models;
use \Phalcon\Mvc\ModelInterface;

class Blog extends \Phalcon\Mvc\Model
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
    protected $revision;

    /**
     *
     * @var string
     */
    protected $title_clean;

    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var string
     */
    protected $enabled;

    /**
     *
     * @var string
     */
    protected $comments;

    /**
     *
     * @var string
     */
    protected $featured;

    /**
     *
     * @var integer
     */
    protected $author_id;

    /**
     *
     * @var string
     */
    protected $style;

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
     * Method to set the value of field revision
     *
     * @param integer $revision
     * @return $this
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;

        return $this;
    }

    /**
     * Method to set the value of field title_clean
     *
     * @param string $title_clean
     * @return $this
     */
    public function setTitleClean($title_clean)
    {
        $this->title_clean = $title_clean;

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
     * Method to set the value of field comments
     *
     * @param string $comments
     * @return $this
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Method to set the value of field featured
     *
     * @param string $featured
     * @return $this
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;

        return $this;
    }

    /**
     * Method to set the value of field author_id
     *
     * @param integer $author_id
     * @return $this
     */
    public function setAuthorId($author_id)
    {
        $this->author_id = $author_id;

        return $this;
    }

    /**
     * Method to set the value of field style
     *
     * @param string $style
     * @return $this
     */
    public function setStyle($style)
    {
        $this->style = $style;

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
     * Returns the value of field revision
     *
     * @return integer
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Returns the value of field title_clean
     *
     * @return string
     */
    public function getTitleClean()
    {
        return $this->title_clean;
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
     * Returns the value of field enabled
     *
     * @return string
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Returns the value of field comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Returns the value of field featured
     *
     * @return string
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Returns the value of field author_id
     *
     * @return integer
     */
    public function getAuthorId()
    {
        return $this->author_id;
    }

    /**
     * Returns the value of field style
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("blog");
        $this->hasMany('id', 'App\Models\BlogComment', 'blog_id', ['alias' => 'BlogComment']);
        $this->hasMany('id', 'App\Models\BlogMeta', 'blog_id', ['alias' => 'BlogMeta']);
        $this->hasMany('id', 'App\Models\BlogRelated', 'blog_related_id', ['alias' => 'BlogRelated']);
        $this->hasMany('id', 'App\Models\BlogRevision', 'blog_id', ['alias' => 'BlogRevision']);
        $this->hasMany('id', 'App\Models\BlogTag', 'blog_id', ['alias' => 'BlogTag']);
        $this->hasMany('id', 'App\Models\BlogToCategory', 'blog_id', ['alias' => 'BlogToCategory']);
        $this->hasMany('id', 'App\Models\Event', 'blogid', ['alias' => 'Event']);
        $this->hasMany('id', 'App\Models\FileUpload', 'blog_id', ['alias' => 'FileUpload']);
        $this->belongsTo('style', 'App\Models\BlogStyle', 'style_class', ['alias' => 'BlogStyle']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Blog[]|Blog|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Blog|\Phalcon\Mvc\Model\ResultInterface
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
            'revision' => 'revision',
            'title_clean' => 'title_clean',
            'title' => 'title',
            'enabled' => 'enabled',
            'comments' => 'comments',
            'featured' => 'featured',
            'author_id' => 'author_id',
            'style' => 'style'
        ];
    }

}
