<?php

namespace App\Models;

class BlogCategory extends \Phalcon\Mvc\Model
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
    protected $name_clean;

    /**
     *
     * @var string
     */
    protected $enabled;

    /**
     *
     * @var string
     */
    protected $date_created;

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
     * Method to set the value of field name_clean
     *
     * @param string $name_clean
     * @return $this
     */
    public function setNameClean($name_clean)
    {
        $this->name_clean = $name_clean;

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
     * Method to set the value of field date_created
     *
     * @param string $date_created
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;

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
     * Returns the value of field name_clean
     *
     * @return string
     */
    public function getNameClean()
    {
        return $this->name_clean;
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
     * Returns the value of field date_created
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pcan");
        $this->setSource("blog_category");
        $this->hasMany('id', 'App\Models\BlogToCategory', 'category_id', ['alias' => 'BlogToCategory']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogCategory[]|BlogCategory|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogCategory|\Phalcon\Mvc\Model\ResultInterface
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
            'name' => 'name',
            'name_clean' => 'name_clean',
            'enabled' => 'enabled',
            'date_created' => 'date_created'
        ];
    }

}
