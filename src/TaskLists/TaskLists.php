<?php

namespace Scriptotek\Alma\TaskLists;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Conf\Library;

class TaskLists
{
    protected $client;

    /**
     * TaskLists constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getLendingRequests(Library $library, $params = [])
    {
        return new LendingRequests($this->client, $library, $params);
    }

    public function getRequestedResources(Library $library, $circ_desk = 'DEFAULT_CIRC_DESK', $params = [])
    {
        return new RequestedResources($this->client, $library, $circ_desk, $params);
    }
}
