<?php

namespace App\Models;
use \Phalcon\Mvc\ModelInterface;

class Meta extends \Phalcon\Mvc\Model
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
    protected $meta_name;

    /**
     *
     * @var string
     */
    protected $template;

    /**
     *
     * @var integer
     */
    protected $data_limit;

    /**
     *
     * @var string
     */
    protected $display;

    /**
     *
     * @var string
     */
    protected $prefix_site;

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
     * Method to set the value of field meta_name
     *
     * @param string $meta_name
     * @return $this
     */
    public function setMetaName($meta_name)
    {
        $this->meta_name = $meta_name;

        return $this;
    }

    /**
     * Method to set the value of field template
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Method to set the value of field data_limit
     *
     * @param integer $data_limit
     * @return $this
     */
    public function setDataLimit($data_limit)
    {
        $this->data_limit = $data_limit;

        return $this;
    }

    /**
     * Method to set the value of field display
     *
     * @param string $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * Method to set the value of field prefix_site
     *
     * @param string $prefix_site
     * @return $this
     */
    public function setPrefixSite($prefix_site)
    {
        $this->prefix_site = $prefix_site;

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
     * Returns the value of field meta_name
     *
     * @return string
     */
    public function getMetaName()
    {
        return $this->meta_name;
    }

    /**
     * Returns the value of field template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Returns the value of field data_limit
     *
     * @return integer
     */
    public function getDataLimit()
    {
        return $this->data_limit;
    }

    /**
     * Returns the value of field display
     *
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Returns the value of field prefix_site
     *
     * @return string
     */
    public function getPrefixSite()
    {
        return $this->prefix_site;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("meta");
        $this->hasMany('id', 'App\Models\BlogMeta', 'meta_id', ['alias' => 'BlogMeta']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Meta[]|Meta|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Meta|\Phalcon\Mvc\Model\ResultInterface
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
            'meta_name' => 'meta_name',
            'template' => 'template',
            'data_limit' => 'data_limit',
            'display' => 'display',
            'prefix_site' => 'prefix_site'
        ];
    }

}
