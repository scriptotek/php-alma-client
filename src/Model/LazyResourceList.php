<?php

namespace Scriptotek\Alma\Model;

use Scriptotek\Alma\Client;

/**
 * The LazyResourceList extends the LazyResource class with functionality for
 * working with lists of resources, such as holdings, items, loans, etc.
 */
abstract class LazyResourceList extends LazyResource implements \Countable
{
    /* @var array */
    protected $resources = [];

    /**
     * The key in the response object that points to the resource list.
     *
     * @var string
     */
    protected $responseKey;

    /**
     * LazyResourceList constructor.
     *
     * @param Client $client
     * @param string $responseKey
     */
    public function __construct(Client $client, $responseKey)
    {
        parent::__construct($client);
        $this->responseKey = $responseKey;
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     * @return mixed
     */
    abstract protected function convertToResource($data);

    /**
     * Called when data is available to be processed.
     *
     * @param mixed $data
     */
    public function onData($data)
    {
        $this->resources = ($data->total_record_count === 0) ? [] : array_map(
            function (\stdClass $res) {
                return $this->convertToResource($res);
            },
            $data->{$this->responseKey}
        );
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return isset($data->total_record_count);
    }

    /**
     * Get all the resources.
     *
     * @return array
     */
    public function all()
    {
        return $this->init()->resources;
    }

    /**
     * Number of resources.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The number of resources as an integer.
     */
    public function count()
    {
        return count($this->all());
    }
}
