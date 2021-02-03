<?php

namespace WC\Models;
use \Phalcon\Mvc\ModelInterface;

class BlogComment extends \Phalcon\Mvc\Model
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
    protected $blog_id;

    /**
     *
     * @var integer
     */
    protected $user_id;

    /**
     *
     * @var integer
     */
    protected $head_id;

    /**
     *
     * @var integer
     */
    protected $reply_to_id;

    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var string
     */
    protected $comment;

    /**
     *
     * @var string
     */
    protected $mark_read;

    /**
     *
     * @var string
     */
    protected $enabled;

    /**
     *
     * @var string
     */
    protected $date_comment;

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
     * Method to set the value of field user_id
     *
     * @param integer $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Method to set the value of field head_id
     *
     * @param integer $head_id
     * @return $this
     */
    public function setHeadId($head_id)
    {
        $this->head_id = $head_id;

        return $this;
    }

    /**
     * Method to set the value of field reply_to_id
     *
     * @param integer $reply_to_id
     * @return $this
     */
    public function setReplyToId($reply_to_id)
    {
        $this->reply_to_id = $reply_to_id;

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
     * Method to set the value of field comment
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Method to set the value of field mark_read
     *
     * @param string $mark_read
     * @return $this
     */
    public function setMarkRead($mark_read)
    {
        $this->mark_read = $mark_read;

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
     * Method to set the value of field date_comment
     *
     * @param string $date_comment
     * @return $this
     */
    public function setDateComment($date_comment)
    {
        $this->date_comment = $date_comment;

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
     * Returns the value of field blog_id
     *
     * @return integer
     */
    public function getBlogId()
    {
        return $this->blog_id;
    }

    /**
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field head_id
     *
     * @return integer
     */
    public function getHeadId()
    {
        return $this->head_id;
    }

    /**
     * Returns the value of field reply_to_id
     *
     * @return integer
     */
    public function getReplyToId()
    {
        return $this->reply_to_id;
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
     * Returns the value of field comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Returns the value of field mark_read
     *
     * @return string
     */
    public function getMarkRead()
    {
        return $this->mark_read;
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
     * Returns the value of field date_comment
     *
     * @return string
     */
    public function getDateComment()
    {
        return $this->date_comment;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("blog_comment");
        $this->belongsTo('blog_id', 'WC\Models\Blog', 'id', ['alias' => 'Blog']);
        $this->belongsTo('user_id', 'WC\Models\Users', 'id', ['alias' => 'Users']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogComment[]|BlogComment|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogComment|\Phalcon\Mvc\Model\ResultInterface
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
            'blog_id' => 'blog_id',
            'user_id' => 'user_id',
            'head_id' => 'head_id',
            'reply_to_id' => 'reply_to_id',
            'title' => 'title',
            'comment' => 'comment',
            'mark_read' => 'mark_read',
            'enabled' => 'enabled',
            'date_comment' => 'date_comment'
        ];
    }

}
