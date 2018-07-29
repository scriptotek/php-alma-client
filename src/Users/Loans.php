<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\SimplePagedLazyResourceList;

/**
 * Iterable collection of Loan resources belonging to some User.
 */
class Loans extends SimplePagedLazyResourceList implements \Countable, \Iterator
{
    /* @var User */
    public $user;

    public function __construct(Client $client, User $user)
    {
        parent::__construct($client);
        $this->user = $user;
    }

    /**
     * Get resource.
     *
     * @param string $loan_id
     * @return Loan
     */
    public function get($loan_id)
    {
        return Loan::make($this->client, $this->user, $loan_id);
    }

    /**
     * Convert a retrieved resource object to a model object.
     *
     * @param $data
     * @return Loan
     */
    public function convertToResource($data)
    {
        return $this->get($data->loan_id)->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/users/{$this->user->id}/loans";
    }
}
