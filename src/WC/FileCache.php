<?php
namespace WC;

use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;

/** 
 * Somehow this failed to usable as a trait to return $adapter directly
 */
class FileCache
{
    private $adapter;
    
    public function __construct(array $options) {
        $serializerFactory = new SerializerFactory();
        $this->adapter = new Stream($serializerFactory, $options);
    }
    public function get($key)
    {
        return $this->adapter->get($key, $default = null);
    }
    public function set($key, $value) {
        $this->adapter->set($key, $value);
    }

}
