<?php

namespace Scriptotek\Alma;

class ResourceList implements \Iterator, \Countable, \ArrayAccess
{
    public $client;
    public $factory;
    protected $resourceName;

    public function __construct($client, $factory)
    {
        $this->client = $client;
        $this->factory = $factory ?: new Factory();
    }

    /**
     * Returns a single resource.
     *
     * @param $id
     *
     * @return ResourceInterface
     */
    public function getResource($id)
    {
        return $this->factory->make(
            $this->resourceName,
            $this->getFactoryArgs($id),
            $this->client
        );
    }

    /**
     * Returns all resources.
     */
    public function getResources()
    {
        // No endpoint available...
        throw new \ErrorException('Listing all resources not supported by Alma');
    }

    /*********************************************************
     * Iterator + Countable
     *********************************************************/

    protected $position = 0;
    protected $_resources;

    public function current()
    {
        return $this->factory->make(
            $this->resourceName,
            $this->getFactoryArgs($this->resources()[$this->position]),
            $this->client
        );
    }

    public function resources($force = false)
    {
        if ($force || !isset($this->_resources)) {
            $this->_resources = $this->getResources();
        }

        return $this->_resources;
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return $this->position < $this->count();
    }

    public function count()
    {
        return count($this->getResources());
    }

    /*********************************************************
     * ArrayAccess
     *********************************************************/

    public function offsetExists($key)
    {
        return true;
    }

    public function offsetGet($key)
    {
        return $this->getResource($key);
    }

    public function offsetSet($key, $value)
    {
        // Uh oh
    }

    public function offsetUnset($key)
    {
        // Uh oh
    }
}
