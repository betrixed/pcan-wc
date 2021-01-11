<?php

namespace WC\Models;
use \Phiz\Mvc\ModelInterface;

class Linktogallery extends \Phiz\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $gallid;

    /**
     *
     * @var integer
     */
    protected $linkid;

    /**
     *
     * @var string
     */
    protected $visible;

    /**
     * Method to set the value of field gallid
     *
     * @param integer $gallid
     * @return $this
     */
    public function setGallid($gallid)
    {
        $this->gallid = $gallid;

        return $this;
    }

    /**
     * Method to set the value of field linkid
     *
     * @param integer $linkid
     * @return $this
     */
    public function setLinkid($linkid)
    {
        $this->linkid = $linkid;

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
     * Returns the value of field gallid
     *
     * @return integer
     */
    public function getGallid()
    {
        return $this->gallid;
    }

    /**
     * Returns the value of field linkid
     *
     * @return integer
     */
    public function getLinkid()
    {
        return $this->linkid;
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
        $this->setSource("linktogallery");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Linktogallery[]|Linktogallery|\Phiz\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phiz\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Linktogallery|\Phiz\Mvc\Model\ResultInterface
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
            'gallid' => 'gallid',
            'linkid' => 'linkid',
            'visible' => 'visible'
        ];
    }

}
