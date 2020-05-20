<?php

namespace App\Models;

class Blog extends \Phalcon\Mvc\Model
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
    protected $title;

    /**
     *
     * @var string
     */
    protected $article;

    /**
     *
     * @var string
     */
    protected $title_clean;

    /**
     *
     * @var integer
     */
    protected $author_id;

    /**
     *
     * @var string
     */
    protected $date_published;

    /**
     *
     * @var string
     */
    protected $date_updated;

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
     * @var string
     */
    protected $style;

    /**
     *
     * @var integer
     */
    protected $issue;

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
     * Method to set the value of field article
     *
     * @param string $article
     * @return $this
     */
    public function setArticle($article)
    {
        $this->article = $article;

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
     * Method to set the value of field date_published
     *
     * @param string $date_published
     * @return $this
     */
    public function setDatePublished($date_published)
    {
        $this->date_published = $date_published;

        return $this;
    }

    /**
     * Method to set the value of field date_updated
     *
     * @param string $date_updated
     * @return $this
     */
    public function setDateUpdated($date_updated)
    {
        $this->date_updated = $date_updated;

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
     * Method to set the value of field issue
     *
     * @param integer $issue
     * @return $this
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;

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
     * Returns the value of field title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the value of field article
     *
     * @return string
     */
    public function getArticle()
    {
        return $this->article;
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
     * Returns the value of field author_id
     *
     * @return integer
     */
    public function getAuthorId()
    {
        return $this->author_id;
    }

    /**
     * Returns the value of field date_published
     *
     * @return string
     */
    public function getDatePublished()
    {
        return $this->date_published;
    }

    /**
     * Returns the value of field date_updated
     *
     * @return string
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
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
     * Returns the value of field style
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Returns the value of field issue
     *
     * @return integer
     */
    public function getIssue()
    {
        return $this->issue;
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
        $this->hasMany('id', 'App\Models\BlogTag', 'blog_id', ['alias' => 'BlogTag']);
        $this->hasMany('id', 'App\Models\BlogToCategory', 'blog_id', ['alias' => 'BlogToCategory']);
        $this->hasMany('id', 'App\Models\Event', 'blogid', ['alias' => 'Event']);
        $this->hasMany('id', 'App\Models\FileUpload', 'blog_id', ['alias' => 'FileUpload']);
        $this->belongsTo('style', 'App\Models\BlogStyle', 'style_class', ['alias' => 'BlogStyle']);
        $this->belongsTo('author_id', 'App\Models\Users', 'id', ['alias' => 'Users']);
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
            'title' => 'title',
            'article' => 'article',
            'title_clean' => 'title_clean',
            'author_id' => 'author_id',
            'date_published' => 'date_published',
            'date_updated' => 'date_updated',
            'enabled' => 'enabled',
            'comments' => 'comments',
            'featured' => 'featured',
            'style' => 'style',
            'issue' => 'issue'
        ];
    }

}
