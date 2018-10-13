<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

/**
 * Iterable collection of Fee resources.
 */
class Fees extends LazyResourceList implements \Countable, \Iterator, \ArrayAccess
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /**
     * The User object this Fees list belongs to.
     *
     * @var User
     */
    public $user;

    /**
     * Fees constructor.
     *
     * @param Client $client
     * @param User   $user
     */
    public function __construct(Client $client, User $user)
    {
        parent::__construct($client, 'fee');
        $this->user = $user;
    }

    /**
     * Get a single Fee by id.
     *
     * @param string $id
     *
     * @return Fee
     */
    public function get($id)
    {
        return Fee::make($this->client, $this->user, $id);
    }

    /**
     * Convert a retrieved resource object to a model object.
     *
     * @param $data
     *
     * @return Fee
     */
    public function convertToResource($data)
    {
        return $this->get($data->id)->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/users/{$this->user->id}/fees";
    }
}
