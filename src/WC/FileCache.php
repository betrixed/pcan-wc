<?php
namespace WC;

use Phiz\Cache\Adapter\Stream;
use Phiz\Storage\SerializerFactory;
use Phiz\Support\HelperFactory;

/** 
 * Somehow this failed as a trait to return $adapter directly
 */
class FileCache extends HelperFactory
{
    private $adapter;

    public function __construct(array $options, HelperFactory $helper) {
        $this->adapter = new Stream($helper, new SerializerFactory(), $options);
    }
    
    public function clear() {
        $this->adapter->clear();
    }
    
    public function get($key)
    {
        return $this->adapter->get($key, $default = null);
    }
    
    public function set($key, $value) {
        $this->adapter->set($key, $value);
    }

    }
