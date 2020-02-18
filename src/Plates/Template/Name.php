<?php

namespace Plates\Template;

use Plates\Engine;
use LogicException;

/**
 * A template name.
 */
class Name
{
    /**
     * Instance of the template engine.
     * @var Engine
     */
    protected $engine;

    /**
     * The original name.
     * @var string
     */
    protected $name;

    /**
     * The parsed template folder.
     * @var Folder
     */
    protected $folder;

    /**
     * The parsed template filename.
     * @var string
     */
    protected $file;
    
    /**
     * Cache of fully resolved path for this name object
     * @var string 
     */
    protected $resolved;

    /**
     * Create a new Name instance.
     * @param Engine $engine
     * @param string $name
     */
    public function __construct(Engine $engine, $name)
    {
        $this->setEngine($engine);
        $this->setName($name);
    }

    /**
     * Set the engine.
     * @param  Engine $engine
     * @return Name
     */
    public function setEngine(Engine $engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Get the engine.
     * @return Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Set the original name and parse it.
     * @param  string $name
     * @return Name
     */
    public function setName($name)
    {
        $this->name = $name;

        $parts = explode('::', $this->name);

        if (count($parts) === 1) {
            $this->setFile($parts[0]);
        } elseif (count($parts) === 2) {
            $this->setFolder($parts[0]);
            $this->setFile($parts[1]);
        } else {
            throw new LogicException(
                'The template name "' . $this->name . '" is not valid. ' .
                'Do not use the folder namespace separator "::" more than once.'
            );
        }

        return $this;
    }

    /**
     * Get the original name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the parsed template folder.
     * @param  string $folder
     * @return Name
     */
    public function setFolder($folder)
    {
        $this->folder = $this->engine->getFolders()->get($folder);

        return $this;
    }

    /**
     * Get the parsed template folder.
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set the parsed template file.
     * @param  string $file
     * @return Name
     */
    public function setFile($file)
    {
        if ($file === '') {
            throw new LogicException(
                'The template name "' . $this->name . '" is not valid. ' .
                'The template name cannot be empty.'
            );
        }
        /* first of all, check if a file extension is already present */
        $info = pathinfo($file,PATHINFO_EXTENSION);
        if (empty($info)) {
            if (!is_null($this->engine->getFileExtension())) {
                $file .= '.' . $this->engine->getFileExtension();
            }
        }
        $this->file = $file;
        return $this;
    }

    /**
     * Get the parsed template file.
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Resolve path according to list
     * of folders, and default directory.
     * @return string
     */
    public function findPath() {
        if (is_null($this->folder)) {
            // search all folders by insertion sequence
            $fname = null;
            $list = [];
        }
        else {
            $fname = $this->folder->getName();
            $list[] = $this->folder;
        }
        $folders = $this->engine->getFolders();
        // engine could do this with function afterFolders($folder)
        
        $fallbacks = $folders->fallbackList($fname);
        if (!empty($fallbacks)) {
            $list = array_merge($list, $fallbacks);
        }
        foreach($list as $fobj) {
            $path = $fobj->getPath() . DIRECTORY_SEPARATOR . $this->file;
            if (is_file($path)) {
                return $path;
            }
        }
        return $this->getDefaultDirectory() . DIRECTORY_SEPARATOR . $this->file;
    }
    /**
     * Return resolved file path
     * @return string
     */
    public function getPath()
    {
        if (is_null($this->resolved)) {
            $this->resolved = $this->findPath();
        }
        return $this->resolved;
    }

    /**
     * Check if template path exists.
     * @return boolean
     */
    public function doesPathExist()
    {
        return is_file($this->getPath());
    }

    /**
     * Get the default templates directory.
     * @return string
     */
    protected function getDefaultDirectory()
    {
        $directory = $this->engine->getDirectory();

        if (is_null($directory)) {
            throw new LogicException(
                'The template name "' . $this->name . '" is not valid. '.
                'The default directory has not been defined.'
            );
        }

        return $directory;
    }
}
