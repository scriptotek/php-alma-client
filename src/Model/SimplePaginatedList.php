<?php

namespace Scriptotek\Alma\Model;

use Scriptotek\Alma\Exception\ClientException;

/**
 * A SimplePaginatedList is a list that is paged using the `offset`
 * and `limit` parameters and that provides a `totalRecordCount` in the first response,
 * so that we can return a count without having to retrieve all the pages.
 * A list which is not of this type is the Analytics report row list.
 */
abstract class SimplePaginatedList extends LazyResourceList
{
    use PaginatedList;

    /* @var integer */
    protected $offset = 0;

    /* @var integer */
    protected $limit = 10;


    protected function fetchBatch()
    {
        if (!is_null($this->totalRecordCount) && $this->offset >= $this->totalRecordCount) {
            return;
        }

        $response = $this->client->getJSON($this->url('', [
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]));
        return $this->onData($response);
    }

    protected function fetchData()
    {
        do {
            $this->fetchBatch();
        } while (!$this->isInitialized($this->resources));
        return null;
    }

    protected function onData($data)
    {
        parent::onData($data);
        $this->offset = count($this->resources);
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return count($data) === $this->totalRecordCount;
    }

    /**
     * Total number of resources.
     * @link http://php.net/manual/en/countable.count.php
     * @return int
     */
    public function count()
    {
        if (is_null($this->totalRecordCount)) {
            $this->fetchBatch();
        }

        return $this->totalRecordCount;
    }
}
