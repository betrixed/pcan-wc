<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC {

    /**
     * Assigns global instance $WCLOADER
     *
     * @author michael
     */
// Simple loader for 1 or 2 levels of class prefix
    class Loader {

        protected array $prefixPaths;

        static public function instance() {
            global $WCLOADER;
            return $WCLOADER;
        }

        static public function loadfile($file): bool {
            if (file_exists($file)) {
                try {
                    require $file;
                    debugLine("Loaded " . $file);
                    return true;
                } catch (\Exception $ex) {
                    debugLine($ex->getMessage());
                }
            } else {
                debugLine("File not found: " . $file);
            }
            return false;
        }

        static public function loader($class): bool {
            global $WCLOADER;
            return $WCLOADER->load($class);
        }

        public function load($class): bool {
            $pos = strpos($class, "\\");
            if ($pos !== false) {
                $prefix = substr($class, 0, $pos);
                $path = $this->prefixPaths[$prefix] ?? null;
                if (!$path) {
                    debugLine("Class prefix not found: " . $prefix);
                    return false;
                }
                if (is_array($path)) {
                    // 2nd level
                    $pos2 = strpos($class, "\\", $pos + 1);
                    if ($pos2 !== false) {
                        $prefix2 = substr($class, $pos + 1, $pos2 - $pos - 1);
                        $path2 = $path[$prefix2] ?? null;
                    } else {
                        $path2 = null;
                    }
                    if (!$path2) {
                        debugLine("Level 2 prefix not found: ", $prefix2);
                    }
                    $path = $path2;
                    $pos = $pos2;
                }
                if (is_string($path)) {
                    $leaf = str_replace("\\", DIRECTORY_SEPARATOR, substr($class, $pos));
                    $file = $path . $leaf . ".php";
                    debugLine("Class load: " . $file);
                    if (file_exists($file)) {
                        try {
                            require $file;
                            return true;
                        } catch (\Exception $ex) {
                            debugLine($ex->getMessage());
                        }
                    } else {
                        throw new \Exception("class file not found " . $file);
                    }
                }
            }
            return false;
        }

        public function addPathArray(array $config): void {
            foreach ($config as $prefix => $path) {
                $this->prefixPaths[$prefix] = $path;
            }
        }

        public function addPath($prefix, $path): void {
            $this->prefixPaths[$prefix] = $path;
        }

        public function __toString(): string {
            $s = "";
            foreach ($this->prefixPaths as $key => $path) {
                $s .= $key . " : " . $path . PHP_EOL;
            }
            return $s;
        }

        public function register() {
            spl_autoload_register("WC\Loader::loader");
        }

    }

} // namespace WC

namespace {
    // global is necessary
    /**
     * $DEBUG_TRACE either $LOG_TRACE or $ECHO_TRACE is true
     */
    global $DEBUG_TRACE, $LOG_TRACE, $LOG_FILE, $WCLOADER, $ECHO_TRACE;
    if (!isset($LOG_TRACE)) {
        $ECHO_TRACE = false;
    }
    if (!isset($LOG_TRACE)) {
        $LOG_TRACE = false;
    }
    if (!isset($DEBUG_TRACE)) {
        $DEBUG_TRACE = true;
    }
    if (!isset($LOG_FILE)) {
        $LOG_FILE = null;
    }
    
    function debugLine(string $s) {
        global $DEBUG_TRACE, $LOG_TRACE, $ECHO_TRACE, $LOG_FILE;
        global $SITE_FOLDER, $PHP_DIR;
        $DEBUG_TRACE = ($ECHO_TRACE || $LOG_TRACE);
        if ($DEBUG_TRACE) {
            $line = $s . PHP_EOL;
            if ($ECHO_TRACE) {
                echo ($line);
            }
            if ($LOG_TRACE) {
                if (!$LOG_FILE) {
                    $LOG_FILE = $PHP_DIR . "/sites/$SITE_FOLDER/tmp/logs/trace.txt";
                    
                    file_put_contents($LOG_FILE, $line);
                } else {
                    file_put_contents($LOG_FILE, $line, FILE_APPEND);
                }
            }
        }
    }
    $WCLOADER = new WC\Loader();
    return $WCLOADER;
}