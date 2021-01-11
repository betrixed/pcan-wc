<?php

/**
 * Read any config file and make a Phiz\Config object.
 * File path OS conversion and directory termination
 *
 * @author Michael Rynn
 */

namespace WC\Link;


defined('DS') || define('DS', DIRECTORY_SEPARATOR);

class Path   {

    static function startsWith($target, $with) {
        return (substr($target,0, strlen($with)) === $with);
    }
    static function native(string $path) {
        if (DS == '/') {
            $result = str_replace("\\", DS, $path);
        } else {
            $result = str_replace('/', DS, $path);
        }
        return $result;
    }

    /**
     * 
     * Ensure path ends with OS native dir separator.
     * Does nothing if OS separator already terminates path.
     * Otherwise replaces alternate OS separators and adds terminal separator.
     * @param string $path
     * @return string
     */
    static function endSep(string $path) {
        $sep = substr($path, -1);
        if ($sep !== DS) {
            if ($sep == "\\") {
                // This must be unix using path with windows configuations, or a typo
                $result = self::native($path);
            } else if ($sep == '/') {
                // This may be windows, ok, but visually nice if all point same way
                $result = self::native($path);
            } else {
                $result = self::native($path) . DS;
            }
            return $result;
        }
        return $path;
        
    }
    
    static public function deleteAllFiles($globpath)
    {
       foreach(glob($globpath) as $file) {
            unlink($file); 
        }
    }
    /** 
     * Ensure path does not end with a directory separator character.
     * @param string $path
     * @return string
     */
    
    static function noEndSep(string $path) {
        $sep = substr($path, -1);
        if ($sep == "\\" || $sep == '/') {
            $result = substr($path, 0, strlen($path) - 1);
            return ($sep == DS) ? $result : self::native($result);
        }
        return $path;
    }
    
   
    
    
    /** 
     * Return the first matching root path from this.
     * Such as to find a view directory or conficuation file.
     * @param array $viewDirs To find which one to be returned. 
     *         Trailing seperator expected.
     * @param string $pathFile Eg 'path/file'
     * @param array $fileTypes Extensions with dot ['.volt', '.phtml']
     * @return false if no match, else array of [ path, fileType ]
     */
    static function findFirstPath(array $viewDirs, string $pathFile, array $fileTypes) {
        foreach($viewDirs as $vpath) {
            $testPath = $vpath . $pathFile;
            foreach($fileTypes as $extType) {
                if (file_exists($testPath . $extType)) {
                    return [$vpath, $extType];
                }
            }
        }
        return false;
    }
    
}
