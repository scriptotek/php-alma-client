<?php

namespace Scriptotek\Alma\TaskLists;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\Model\SimplePaginatedList;

class RequestedResources extends SimplePaginatedList implements \Countable, \Iterator
{
    protected $client;
    protected $library;

    /**
     * RequestedResources constructor.
     *
     * @param Client  $client
     * @param Library $library
     * @param string  $circ_desk
     * @param array   $params
     */
    public function __construct(Client $client, Library $library, $circ_desk = 'DEFAULT_CIRC_DESK', $params = [])
    {
        parent::__construct($client, 'requested_resource');
        $this->library = $library;
        $params['library'] = $library->code;
        $params['circ_desk'] = $circ_desk;
        $this->params = $params;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return '/task-lists/requested-resources';
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     *
     * @return mixed
     */
    protected function convertToResource($data)
    {
        $bib = $this->client->bibs->get($data->resource_metadata->mms_id->value);

        return RequestedResource::make($this->client, $this->library, $this->params['circ_desk'], $bib)
            ->init($data);
    }
}
