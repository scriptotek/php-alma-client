<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\SimplePaginatedList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;
use Scriptotek\Alma\Conf\Job;

/**
 * Iterable collection of Jobs.
 */
class Jobs extends SimplePaginatedList implements \ArrayAccess, \Countable, \Iterator
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

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
     * @return Library
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
     * @return Library
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
