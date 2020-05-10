<?php
namespace WC;

use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;
use WC\App;

/** 
 * Somehow this failed as a trait to return $adapter directly
 */
class FileCache
{
    private $adapter;

    public function __construct(array $options) {
        $serializerFactory = new SerializerFactory();
        $this->adapter = new Stream($serializerFactory, $options);
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

    static public function modelCache() : FileCache
    {
        return new FileCache(App::instance()->model_cache);
    }
}
