<?php

namespace WC\Models;
use \Phalcon\Mvc\ModelInterface;

class ImgGallery extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $imageid;

    /**
     *
     * @var integer
     */
    protected $galleryid;

    /**
     *
     * @var string
     */
    protected $visible;

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
     * Method to set the value of field visible
     *
     * @param string $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
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
     * Returns the value of field galleryid
     *
     * @return integer
     */
    public function getGalleryid()
    {
        return $this->galleryid;
    }

    /**
     * Returns the value of field visible
     *
     * @return string
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("img_gallery");
        $this->belongsTo('imageid', 'WC\Models\Image', 'id', ['alias' => 'Image']);
        $this->belongsTo('galleryid', 'WC\Models\Gallery', 'id', ['alias' => 'Gallery']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImgGallery[]|ImgGallery|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImgGallery|\Phalcon\Mvc\Model\ResultInterface
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
            'imageid' => 'imageid',
            'galleryid' => 'galleryid',
            'visible' => 'visible'
        ];
    }

}
