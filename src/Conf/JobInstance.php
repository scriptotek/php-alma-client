<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single JobInstance resource.
 */
class JobInstance extends LazyResource
{
    /** @var Job */
    public $job;

    /** @var string */
    public $job_instance_id;

    /**
     * JobInstance constructor.
     *
     * @param Client $client
     * @param Job    $job
     * @param string $job_instance_id
     */
    public function __construct(Client $client, Job $job, string $job_instance_id)
    {
        $this->job = $job;
        $this->job_instance_id = $job_instance_id;
        parent::__construct($client);
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
        return isset($data->job_info);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/conf/jobs/{$this->job->job_id}/instances/{$this->job_instance_id}";
    }
}
