<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single Fee resource.
 */
class Fee extends LazyResource
{
    /* @var User */
    protected $user;

    /* @var string */
    protected $id;

    public function __construct(Client $client, User $user, $id)
    {
        parent::__construct($client);
        $this->user = $user;
        $this->id = $id;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return sprintf('/users/%s/fees/%s', rawurlencode($this->user->id), $this->id);
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
        return isset($data->link);
    }

    /**
     * Get the related Item, if any.
     *
     * @return Item|null
     */
    public function getItem()
    {
        if (isset($this->barcode)) {
            return $this->client->items->fromBarcode($this->barcode);
        }
    }
}
