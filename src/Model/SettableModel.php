<?php

namespace Scriptotek\Alma\Model;

use Scriptotek\Alma\Model\Model;

abstract class SettableModel extends Model
{
    /**
     * Magic setter function to set properties on the underlying data object
     * 
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        // Convert electronic_collections to ElectronicCollections
        $key_s = implode('', array_map(function ($x) {
            return ucfirst($x);
        }, explode('_', $key)));

        // If there's a setter method, call it.
        $method = 'set' . ucfirst($key_s);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }
        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        // Otherwise set the key on the data object
        $this->data->{$key} = $value;
    }
}
