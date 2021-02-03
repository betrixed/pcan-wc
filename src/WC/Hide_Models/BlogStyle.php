<?php

namespace WC\Models;
use \Phalcon\Mvc\ModelInterface;

class BlogStyle extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $style_class;

    /**
     *
     * @var string
     */
    protected $style_name;

    /**
     * Method to set the value of field style_class
     *
     * @param string $style_class
     * @return $this
     */
    public function setStyleClass($style_class)
    {
        $this->style_class = $style_class;

        return $this;
    }

    /**
     * Method to set the value of field style_name
     *
     * @param string $style_name
     * @return $this
     */
    public function setStyleName($style_name)
    {
        $this->style_name = $style_name;

        return $this;
    }

    /**
     * Returns the value of field style_class
     *
     * @return string
     */
    public function getStyleClass()
    {
        return $this->style_class;
    }

    /**
     * Returns the value of field style_name
     *
     * @return string
     */
    public function getStyleName()
    {
        return $this->style_name;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // 
        $this->setSource("blog_style");
        $this->hasMany('style_class', 'WC\Models\Blog', 'style', ['alias' => 'Blog']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogStyle[]|BlogStyle|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BlogStyle|\Phalcon\Mvc\Model\ResultInterface
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
            'style_class' => 'style_class',
            'style_name' => 'style_name'
        ];
    }

}
