<?php

namespace App\Models;
use \Phalcon\Mvc\ModelInterface;

class BlogRelated extends \Phalcon\Mvc\Model
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
    protected $blog_related_id;

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
     * Method to set the value of field blog_related_id
     *
     * @param integer $blog_related_id
     * @return $this
     */
    public function setBlogRelatedId($blog_related_id)
    {
        $this->blog_related_id = $blog_related_id;

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
     * Returns the value of field blog_related_id
     *
     * @return integer
     */
    public function getBlogRelatedId()
    {
        return $this->blog_related_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("blog_related");
        $this->belongsTo('blog_related_id', 'App\Models\Blog', 'id', ['alias' => 'Blog']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogRelated[]|BlogRelated|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogRelated|\Phalcon\Mvc\Model\ResultInterface
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
            'blog_id' => 'blog_id',
            'blog_related_id' => 'blog_related_id'
        ];
    }

}
