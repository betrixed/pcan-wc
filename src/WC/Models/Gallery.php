<?php

namespace WC\Models;
use \Phiz\Mvc\ModelInterface;

class Gallery extends \Phiz\Mvc\Model
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
    protected $path;

    /**
     *
     * @var string
     */
    protected $description;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $last_upload;

    /**
     *
     * @var string
     */
    protected $leva_path;

    /**
     *
     * @var string
     */
    protected $prava_path;

    /**
     *
     * @var integer
     */
    protected $seriesid;

    /**
     *
     * @var string
     */
    protected $view_thumbs;

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
     * Method to set the value of field last_upload
     *
     * @param string $last_upload
     * @return $this
     */
    public function setLastUpload($last_upload)
    {
        $this->last_upload = $last_upload;

        return $this;
    }

    /**
     * Method to set the value of field leva_path
     *
     * @param string $leva_path
     * @return $this
     */
    public function setLevaPath($leva_path)
    {
        $this->leva_path = $leva_path;

        return $this;
    }

    /**
     * Method to set the value of field prava_path
     *
     * @param string $prava_path
     * @return $this
     */
    public function setPravaPath($prava_path)
    {
        $this->prava_path = $prava_path;

        return $this;
    }

    /**
     * Method to set the value of field seriesid
     *
     * @param integer $seriesid
     * @return $this
     */
    public function setSeriesid($seriesid)
    {
        $this->seriesid = $seriesid;

        return $this;
    }

    /**
     * Method to set the value of field view_thumbs
     *
     * @param string $view_thumbs
     * @return $this
     */
    public function setViewThumbs($view_thumbs)
    {
        $this->view_thumbs = $view_thumbs;

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
     * Returns the value of field path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field last_upload
     *
     * @return string
     */
    public function getLastUpload()
    {
        return $this->last_upload;
    }

    /**
     * Returns the value of field leva_path
     *
     * @return string
     */
    public function getLevaPath()
    {
        return $this->leva_path;
    }

    /**
     * Returns the value of field prava_path
     *
     * @return string
     */
    public function getPravaPath()
    {
        return $this->prava_path;
    }

    /**
     * Returns the value of field seriesid
     *
     * @return integer
     */
    public function getSeriesid()
    {
        return $this->seriesid;
    }

    /**
     * Returns the value of field view_thumbs
     *
     * @return string
     */
    public function getViewThumbs()
    {
        return $this->view_thumbs;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("gallery");
        $this->hasMany('id', 'WC\Models\Image', 'galleryid', ['alias' => 'Image']);
        $this->hasMany('id', 'WC\Models\ImgGallery', 'galleryid', ['alias' => 'ImgGallery']);
        $this->belongsTo('seriesid', 'WC\Models\Series', 'id', ['alias' => 'Series']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Gallery[]|Gallery|\Phiz\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phiz\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Gallery|\Phiz\Mvc\Model\ResultInterface
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
            'path' => 'path',
            'description' => 'description',
            'name' => 'name',
            'last_upload' => 'last_upload',
            'leva_path' => 'leva_path',
            'prava_path' => 'prava_path',
            'seriesid' => 'seriesid',
            'view_thumbs' => 'view_thumbs'
        ];
    }

}
