<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\PaginatedListGenerator;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;
use Scriptotek\Alma\Model\SimplePaginatedList;

/**
 * Iterable collection of Jobs.
 */
class Jobs extends SimplePaginatedList implements \ArrayAccess, \Countable, \Iterator
{
    use ReadOnlyArrayAccess;
    use PaginatedListGenerator;

    /**
     * Job constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, 'job');
    }

    /**
     * Get a single job by job_id.
     *
     * @param string $job_id
     *
     * @return Job
     */
    public function get($job_id)
    {
        return Job::make($this->client, $job_id);
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     *
     * @return Job
     */
    protected function convertToResource($data)
    {
        return Job::make($this->client, $data->id)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return '/conf/jobs';
    }
}
