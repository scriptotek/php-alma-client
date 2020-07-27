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

    /** @var string */
    public $job_id;

    /**
     * Job Instances constructor.
     *
     * @param Client $client
     * @param string $job_id
     */
    public function __construct(Client $client, $job_id)
    {
        $this->job_id = $job_id;
        parent::__construct($client, 'job_instance');
    }

    /**
     * Get a single Job Instance by its job_id and instance_id.
     *
     * @param string $job_id
     * @param string $instance_id
     *
     * @return JobInstance
     */
    public function get($job_id, $instance_id)
    {
        return JobInstance::make($this->client, $job_id, $instance_id);
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
        return JobInstance::make($this->client, $data->job_info->id, $data->id)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/conf/jobs/{$this->job_id}/instances";
    }
}
