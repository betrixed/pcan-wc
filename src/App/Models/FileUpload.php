<?php

namespace App\Models;
use \Phalcon\Mvc\ModelInterface;

class FileUpload extends \Phalcon\Mvc\Model
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
    protected $name;

    /**
     *
     * @var string
     */
    protected $path;

    /**
     *
     * @var string
     */
    protected $mime_type;

    /**
     *
     * @var string
     */
    protected $date_upload;

    /**
     *
     * @var integer
     */
    protected $blog_id;

    /**
     *
     * @var integer
     */
    protected $file_size;

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
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field path
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Method to set the value of field mime_type
     *
     * @param string $mime_type
     * @return $this
     */
    public function setMimeType($mime_type)
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    /**
     * Method to set the value of field date_upload
     *
     * @param string $date_upload
     * @return $this
     */
    public function setDateUpload($date_upload)
    {
        $this->date_upload = $date_upload;

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
     * Method to set the value of field file_size
     *
     * @param integer $file_size
     * @return $this
     */
    public function setFileSize($file_size)
    {
        $this->file_size = $file_size;

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
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the value of field mime_type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }

    /**
     * Returns the value of field date_upload
     *
     * @return string
     */
    public function getDateUpload()
    {
        return $this->date_upload;
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
     * Returns the value of field file_size
     *
     * @return integer
     */
    public function getFileSize()
    {
        return $this->file_size;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("file_upload");
        $this->belongsTo('blog_id', 'App\Models\Blog', 'id', ['alias' => 'Blog']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FileUpload[]|FileUpload|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FileUpload|\Phalcon\Mvc\Model\ResultInterface
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
            'name' => 'name',
            'path' => 'path',
            'mime_type' => 'mime_type',
            'date_upload' => 'date_upload',
            'blog_id' => 'blog_id',
            'file_size' => 'file_size'
        ];
    }

}
