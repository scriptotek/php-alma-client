<?php

namespace Scriptotek\Alma\Model;

use Scriptotek\Alma\Client;

/**
 * The Model class is our base class.
 */
abstract class Model implements \JsonSerializable
{
    /* @var Client */
    protected $client;

    /* @var \stdClass|array */
    protected $data;

    public function __construct(Client $client, $data = null)
    {
        $this->client = $client;
        $this->data = $data;
    }

    /**
     * @param Client $client
     * @param array  ...$params
     *
     * @return static
     */
    public static function make($client, ...$params)
    {
        return new static($client, ...$params);
    }

    /**
     * Load data onto this object. Chainable method.
     *
     * @param \stdClass $data
     *
     * @return self
     */
    public function init($data = null)
    {
        if (!is_null($data)) {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * Get the raw data object.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Magic!
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        // If there's a getter method, call it.
        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        $this->init();

        // If the property is defined in our data object, return it.
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
