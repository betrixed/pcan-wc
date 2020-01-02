<?php

namespace Plates\Template;

use LogicException;

/**
 * A collection of template folders.
 */
class Folders {

    /**
     * Array of template folders.
     * @var array
     */
    protected $folders = array();

    /**
     * Add a template folder.
     * @param  string  $name
     * @param  string  $path
     * @return Folders
     */
    public function add($name, $path) {
        if ($this->exists($name)) {
            throw new LogicException('The template folder "' . $name . '" is already being used.');
        }

        $this->folders[$name] = new Folder($name, $path);

        return $this;
    }

    /**
     * Return a list of folders for fallback starting from $name
     * @param string $name
     * This depends on insertion order into folders array, not name key.
     * If name is null, return the folders in insertion order
     */
    public function fallbackList($name = null) {
        $list = [];
        $found = is_null($name);
        foreach ($this->folders as $fobj) {
            if ($found) {
                    $list[] = $fobj;
            } else {
                $found = ($fobj->getName() === $name);
            }
        }
        return $list;
    }

    /**
     * Remove a template folder.
     * @param  string  $name
     * @return Folders
     */
    public function remove($name) {
        if (!$this->exists($name)) {
            throw new LogicException('The template folder "' . $name . '" was not found.');
        }

        unset($this->folders[$name]);

        return $this;
    }

    /**
     * Get a template folder.
     * @param  string $name
     * @return Folder
     */
    public function get($name) {
        if (!$this->exists($name)) {
            throw new LogicException('The template folder "' . $name . '" was not found.');
        }

        return $this->folders[$name];
    }

    /**
     * Check if a template folder exists.
     * @param  string  $name
     * @return boolean
     */
    public function exists($name) {
        return isset($this->folders[$name]);
    }

}
