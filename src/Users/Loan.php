<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single Loan resource.
 */
class Loan extends LazyResource
{
    /* @var User */
    protected $user;

    /* @var Item */
    protected $item;

    /* @var string */
    protected $loan_id;

    public function __construct(Client $client, User $user, Item $item, $loan_id)
    {
        parent::__construct($client);
        $this->user = $user;
        $this->item = $item;
        $this->loan_id = $loan_id;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/users/{$this->user->id}/loans/{$this->loan_id}";
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return isset($data->loan_id);
    }
}
