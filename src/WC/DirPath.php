<?php

/**
 * Painful nit picking 
 * The convention of PHP, is that directory path constants
 * have no  DIRECTORY_SEPARATOR at the end.
 */
namespace WC;

class DirPath {

// Phalcon wants fiew paths to end with DIRECTORY_SEPARATOR
    static public function pushInFirst($paths, $old_paths): array {
        $path = self::addSep($paths);
        if (is_array($path)) {
            $newPaths = $path;
        } else {
            $newPaths = [$path];
        }


        if (is_string($old_paths)) {
            $old_paths = [$old_paths];
        }
        foreach ($old_paths as $op) {
            if (!in_array($op, $newPaths)) {
                $newPaths[] = $op;
            }
        }
        return $newPaths;
    }

    // string or array of  strings must end in DIRECTORY SEPARATOR
    // returns string or array of strings
    static function addSep($mixed) {
        if (is_array($mixed)) {
            $result = [];
            foreach ($mixed as $value) {
                if (!str_ends_with($value, DIRECTORY_SEPARATOR)) {
                    $result[] = $value . DIRECTORY_SEPARATOR;
                } else {
                    $result[] = $value;
                }
            }
            return $result;
        } else {
            if (!str_ends_with($mixed, DIRECTORY_SEPARATOR)) {
                return $mixed . DIRECTORY_SEPARATOR;
            } else {
                return $mixed;
            }
        }
    }

}
