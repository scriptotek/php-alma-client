<?php

namespace Scriptotek\Alma\Model;

use Scriptotek\Alma\Client;

/**
 * The LazyResourceList extends the LazyResource class with functionality for
 * working with non-paginated lists of resources, such as holdings, items, loans, etc.
 */
abstract class LazyResourceList extends LazyResource implements \Countable
{
    /* @var integer */
    protected $totalRecordCount = null;

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
     *
     * @return mixed
     */
    abstract protected function convertToResource($data);

    /**
     * Called when data is available on the object.
     * The resource classes can use this method to process the data.
     *
     * @param $data
     */
    protected function onData($data)
    {
        if (is_null($this->totalRecordCount)) {
            $this->totalRecordCount = $data->total_record_count;
        }

        if (!isset($data->{$this->responseKey})) {
            return;
        }

        foreach ($data->{$this->responseKey} as $result) {
            $this->resources[] = $this->convertToResource($result);
        }
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     *
     * @return bool
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
     */
    public function count(): int
    {
        return count($this->all());
    }
}
