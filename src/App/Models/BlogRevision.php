<?php

namespace App\Models;

class BlogRevision extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $blog_id;

    /**
     *
     * @var integer
     */
    protected $revision;

    /**
     *
     * @var string
     */
    protected $date_saved;

    /**
     *
     * @var string
     */
    protected $content;

    /**
     * Method to set the value of field blog_id
     *
     * @param integer $blog_id
     * @return $this
     */
    public function setBlogId($blog_id)
    {
        $this->blog_id = $blog_id;

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
     * Method to set the value of field date_saved
     *
     * @param string $date_saved
     * @return $this
     */
    public function setDateSaved($date_saved)
    {
        $this->date_saved = $date_saved;

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
     * Returns the value of field blog_id
     *
     * @return integer
     */
    public function getBlogId()
    {
        return $this->blog_id;
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
     * Returns the value of field date_saved
     *
     * @return string
     */
    public function getDateSaved()
    {
        return $this->date_saved;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("blog_revision");
        $this->belongsTo('blog_id', 'App\Models\Blog', 'id', ['alias' => 'Blog']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogRevision[]|BlogRevision|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogRevision|\Phalcon\Mvc\Model\ResultInterface
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
            'blog_id' => 'blog_id',
            'revision' => 'revision',
            'date_saved' => 'date_saved',
            'content' => 'content'
        ];
    }

}
