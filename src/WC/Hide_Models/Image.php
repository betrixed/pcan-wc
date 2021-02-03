<?php

namespace WC\Models;
use \Phalcon\Mvc\ModelInterface;

class Image extends \Phalcon\Mvc\Model
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
     * @var integer
     */
    protected $width;

    /**
     *
     * @var integer
     */
    protected $height;

    /**
     *
     * @var integer
     */
    protected $galleryid;

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
    protected $file_size;

    /**
     *
     * @var string
     */
    protected $description;

    /**
     *
     * @var integer
     */
    protected $tiedimage;

    /**
     *
     * @var string
     */
    protected $size_str;

    /**
     *
     * @var string
     */
    protected $thumb_ext;

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
     * Method to set the value of field width
     *
     * @param integer $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Method to set the value of field height
     *
     * @param integer $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Method to set the value of field galleryid
     *
     * @param integer $galleryid
     * @return $this
     */
    public function setGalleryid($galleryid)
    {
        $this->galleryid = $galleryid;

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
     * Method to set the value of field tiedimage
     *
     * @param integer $tiedimage
     * @return $this
     */
    public function setTiedimage($tiedimage)
    {
        $this->tiedimage = $tiedimage;

        return $this;
    }

    /**
     * Method to set the value of field size_str
     *
     * @param string $size_str
     * @return $this
     */
    public function setSizeStr($size_str)
    {
        $this->size_str = $size_str;

        return $this;
    }

    /**
     * Method to set the value of field thumb_ext
     *
     * @param string $thumb_ext
     * @return $this
     */
    public function setThumbExt($thumb_ext)
    {
        $this->thumb_ext = $thumb_ext;

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
     * Returns the value of field width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Returns the value of field height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Returns the value of field galleryid
     *
     * @return integer
     */
    public function getGalleryid()
    {
        return $this->galleryid;
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
     * Returns the value of field file_size
     *
     * @return integer
     */
    public function getFileSize()
    {
        return $this->file_size;
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
     * Returns the value of field tiedimage
     *
     * @return integer
     */
    public function getTiedimage()
    {
        return $this->tiedimage;
    }

    /**
     * Returns the value of field size_str
     *
     * @return string
     */
    public function getSizeStr()
    {
        return $this->size_str;
    }

    /**
     * Returns the value of field thumb_ext
     *
     * @return string
     */
    public function getThumbExt()
    {
        return $this->thumb_ext;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("image");
        $this->hasMany('id', 'WC\Models\ImgGallery', 'imageid', ['alias' => 'ImgGallery']);
        $this->belongsTo('galleryid', 'WC\Models\Gallery', 'id', ['alias' => 'Gallery']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Image[]|Image|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Image|\Phalcon\Mvc\Model\ResultInterface
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
            'width' => 'width',
            'height' => 'height',
            'galleryid' => 'galleryid',
            'mime_type' => 'mime_type',
            'date_upload' => 'date_upload',
            'file_size' => 'file_size',
            'description' => 'description',
            'tiedimage' => 'tiedimage',
            'size_str' => 'size_str',
            'thumb_ext' => 'thumb_ext'
        ];
    }

}
