<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;
use Scriptotek\Alma\Conf\JobInstances;

/**
 * A single Job resource.
 */
class Job extends LazyResource
{
    /** @var string */
    public $job_id;

    /** @var JobInstances */
    public $instances;

    /**
     * Job constructor.
     *
     * @param Client $client
     * @param string $job_id
     */
    public function __construct(Client $client, $job_id)
    {
        parent::__construct($client);
        $this->job_id = $job_id;
        $this->instances = new JobInstances($client, $job_id);
    }

    /**
     * Submit the job for running
     *
     * @return string The API response body
     */
    public function submit()
    {
        return $this->client->post($this->url().'?op=run', json_encode($this->jsonSerialize()));
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
        return isset($data->name);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/conf/jobs/{$this->job_id}";
    }
}
