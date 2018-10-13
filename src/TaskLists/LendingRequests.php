<?php

namespace Scriptotek\Alma\TaskLists;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\LazyResourceList;

/**
 * Note to future self:
 * The lending requests API returns maximum 100 requests and does not suport pagination.
 * The response does include a "total_record_count" field just like the APIs that *do* support
 * pagination though, so it almost seems like they just forgot to finalize the pagination support.
 * If pagination arrives in the future, we should extend SimplePaginatedList rather than LazyResourceList.
 */
class LendingRequests extends LazyResourceList implements \Countable, \Iterator
{
    use IterableCollection;

    protected $client;
    protected $library;

    /**
     * LendingRequests constructor.
     *
     * @param Client $client
     * @param Library $library
     * @param array $params
     */
    public function __construct(Client $client, Library $library, $params = [])
    {
        parent::__construct($client, 'user_resource_sharing_request');
        $this->library = $library;
        $params['library'] = $library->code;
        $this->params = $params;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return '/task-lists/rs/lending-requests';
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     * @return mixed
     */
    protected function convertToResource($data)
    {
        return ResourceSharingRequest::make($this->client, $data->request_id)
            ->init($data);
    }
}
