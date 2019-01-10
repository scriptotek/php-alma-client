<?php

namespace Scriptotek\Alma\Electronic;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single Collection resource.
 */
class Collection extends LazyResource
{
    /* @var string */
    public $collection_id;

    /* @var Services */
    public $services;

    public function __construct(Client $client, $collection_id)
    {
        parent::__construct($client);
        $this->collection_id = $collection_id;
        // FUTURE: $this->services = Services::make($this->client, $this);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/electronic/e-collections/{$this->collection_id}";
    }

    /**
     * Get the services for this collection.
     */
//    public function getServices()
//    {
//        return $this->services;
//    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     *
     * @return bool
     */
    protected function isInitialized($data)
    {
        return isset($data->public_name);
    }
}
