<?php

namespace WC;

class Dos {

    static function makedir($path, $permissions = 0777) {
        return is_dir($path) || mkdir($path, $permissions, true);
    }

    /** 	
     * Recursive copy file tree contents $from, $to
     * Parent directory of $to must exist
     * @param type $from - 
     * @param type $to - 
     * @return type
     */
    static public function copyall($from, $to) {
        $dir = opendir($from);
        static::makedir($to);
        while (false !== ($file = readdir($dir))) {
            if (( $file !== '.') && ( $file !== '..')) {
                $src = $from . '/' . $file;
                $dst = $to . '/' . $file;
                if (is_dir($src)) {
                    static::copyall($src, $dst);
                } else {
                    copy($src, $dst);
                }
            }
        }
    }
    /** unlink each file in array */
   static function rm_all($files) {
        foreach ($files as $file) { // iterate files
            if (is_file($file))
                unlink($file); // delete file
        }
    }
    /** unlink each file in array older than $tsecs */
    static function rm_old($files, $tsecs) {
        $now = time();
        foreach ($files as $file) { // iterate files
            if (is_file($file) && ($now - filemtime($file) >= $tsecs)) {
                @unlink($file);
            }
        }
    }

}
