<?php

namespace Scriptotek\Alma\Model;

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

        $url = $this->url('', [
            'offset' => $this->offset,
            'limit'  => $this->limit,
        ]);

        $response = $this->client->getJSON($url);

        if (is_null($response)) {
            throw new \RuntimeException("Empty response from URL: $url");
        }

        return $this->onData($response);
    }

    protected function fetchData()
    {
        do {
            $this->fetchBatch();
        } while (!$this->isInitialized($this->resources));
    }

    protected function onData($data)
    {
        $before = count($this->resources);
        parent::onData($data);
        $after = count($this->resources);
        $this->offset += $after - $before;
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
        if (is_countable($data)) {
            return count($data) === $this->totalRecordCount;
        }

        return false;
    }

    /**
     * Total number of resources.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int
     */
    public function count()
    {
        if (is_null($this->totalRecordCount)) {
            $this->fetchBatch();
        }

        return $this->totalRecordCount;
    }

    /**
     * Mutate the pagination limit
     *
     * @param int $limit Maximum number of items per page (0  - 100)
     * @throws \RuntimeException
     */
    public function setPaginationLimit($limit)
    {
       if ((int)$limit < 0 || (int)$limit > 100) {
               throw new \RuntimeException('Invalid limit value (0 - 100): '.$limit);
       }
        $this->limit = (int) $limit;
    }

}
