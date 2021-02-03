<?php

namespace WC {

    /**
     * Assigns global instance $WCLOADER
     * Really dump loader class.
     * Simple loader for 1 or 2 levels of class prefix
     * "RootNS => 'Path'
     * OR
     * "RootNS => [ 0 => "Path",  1 = ["SubNS1" => "Path", "SubNS2=>"Path"]]
     * @author michael
     */
// 
    class Loader {

        protected array $prefixPaths;
        protected array $folders = []; // Class file
        
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

        public function addClassFolder($path) {
            if (!file_exists($path) && is_dir($path)) {
                throw new \Exception("Path not found " . $path);
            }
            $this->folders[] = $path;
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
                    $levels = $path;
                    // Resolve $path to a string
                    $pos2 = strpos($class, "\\", $pos + 1);
                    if ($pos2 !== false) {
                        // level 1?
                        $level1 = $levels[1] ?? null;
                        if ($level1) {
                            $prefix2 = substr($class, $pos + 1, $pos2 - $pos - 1);
                            $path2 = $level1[$prefix2] ?? null;
                            if (!$path2) {
                                $path = $levels[0] ?? null;
                        //debugLine("Level 2 prefix not found: ", $prefix2);
                            }
                            else {
                                $path = $path2;
                                $pos = $pos2;
                            }
                        } 
                    }
                    else {
                        // first level
                        $path = $levels[0] ?? null;
                    } 
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
            $leaf = $class . ".php";
            foreach($this->folders as $path) {
                $file = $path . "/" . $leaf;
                if (file_exists($file)) {
                    require $file;
                    return true;
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