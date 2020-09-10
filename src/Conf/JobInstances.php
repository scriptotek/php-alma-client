<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\SimplePaginatedList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;
use Scriptotek\Alma\Conf\JobInstance;

/**
 * Iterable collection of Job Instances.
 */
class JobInstances extends SimplePaginatedList implements \ArrayAccess, \Countable, \Iterator
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /** @var Job */
    public $job;

    /**
     * Job Instances constructor.
     *
     * @param Client $client
     * @param Job $job
     */
    public function __construct(Client $client, Job $job)
    {
        parent::__construct($client, 'job_instance');
        $this->job = $job;
    }

    /**
     * Get a single Job Instance by its instance_id.
     *
     * @param string $instance_id
     *
     * @return JobInstance
     */
    public function get(string $instance_id)
    {
        return JobInstance::make($this->client, $this->job, $instance_id);
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     *
     * @return JobInstance
     */
    protected function convertToResource($data)
    {
        return JobInstance::make($this->client, $this->job, $data->id)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/conf/jobs/{$this->job->job_id}/instances";
    }
}
