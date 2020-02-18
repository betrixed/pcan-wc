<?php

namespace Plates\Template;

use LogicException;

/**
 * A template folder.
 */
class Folder
{
    /**
     * The folder name.
     * @var string
     */
    protected $name;

    /**
     * The folder path.
     * @var string
     */
    protected $path;



    /**
     * Create a new Folder instance.
     * @param string  $name
     * @param string  $path
     */
    public function __construct($name, $path)
    {
        $this->setName($name);
        $this->setPath($path);
    }

    /**
     * Set the folder name.
     * @param  string $name
     * @return Folder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the folder name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the folder path.
     * @param  string $path
     * @return Folder
     */
    public function setPath($path)
    {
        if (!is_dir($path)) {
            throw new LogicException('The specified directory path "' . $path . '" does not exist.');
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Get the folder path.
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

}
