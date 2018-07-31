<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single Request resource.
 */
class Request extends LazyResource
{
    /* @var User */
    public $user;

    /* @var string */
    public $request_id;

    public function __construct(Client $client, User $user, $request_id)
    {
        parent::__construct($client);
        $this->user = $user;
        $this->request_id = $request_id;
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return isset($data->request_type);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/users/{$this->user->id}/requests/{$this->request_id}";
    }
}
