<?php

namespace Ratchet\Traits;

trait DynamicPropertiesTrait
{
    /**
     * Storage for dynamic properties.
     *
     * @var array
     */
    protected $_dynamic_properties = [];

    /**
     * Allow setting dynamic properties.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function __set($key, $value) {
        if (property_exists($this, $key)) {
            $this->_dynamic_properties[$key] = $value;
        }
    }

    /**
     * Get a property that has been declared dynamically
     *
     * @param string $key
     *
     * @return mixed|void
     */
    public function __get($key) {
        if (isset($this->_dynamic_properties[$key])) {
            return $this->_dynamic_properties[$key];
        }
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function __isset($key) {
        return isset($this->_dynamic_properties[$key]);
    }
}
