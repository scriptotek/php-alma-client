<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\GhostResource;
use Scriptotek\Alma\Users\User;

class Item implements GhostResource
{
    /**
     * This class is a ghost object that lazy loads the full record only when needed.
     * If $initialized is false, it means we haven't yet loaded the full record.
     * We can still have incomplete data from a search response.
     */
    protected $initialized = false;

    /** @var Client */
    protected $client;

    /** @var string */
    protected $mms_id;

    /** @var string */
    protected $holding_id;

    /** @var string */
    protected $item_id;

    /* @var \stdClass */
    protected $data;

    /**
     * Item constructor.
     * @param Client $client
     * @param $mms_id
     * @param $holding_id
     * @param $item_id
     */
    public function __construct(Client $client, $mms_id, $holding_id, $item_id)
    {
        $this->client = $client;
        $this->mms_id = $mms_id;
        $this->holding_id = $holding_id;
        $this->item_id = $item_id;
    }

    /**
     * Load data onto this Item object. Chainable method.
     *
     * @param \stdClass $data
     *
     * @return Item
     */
    public function init($data = null)
    {
        if ($this->initialized) {
            return $this;
        }

        if (is_null($data)) {
            $data = $this->client->getJSON("/bibs/{$this->mms_id}/holdings/{$this->holding_id}/items/{$this->item_id}");
        }

        if (isset($data->item_data)) {
            $this->initialized = true;
        }

        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->init()->data;
    }
    }

    public function __get($key)
    {
        if (isset($this->data->item_data->{$key})) {
            return $this->data->item_data->{$key};
        }
        if (isset($this->data->holding_data->{$key})) {
            return $this->data->holding_data->{$key};
        }
        if (isset($this->data->bib_data->{$key})) {
            return $this->data->bib_data->{$key};
        }
    }
}
