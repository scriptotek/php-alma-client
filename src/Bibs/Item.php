<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\GhostModel;

class Item extends GhostModel
{
    /** @var Bib */
    public $bib;

    /** @var Holding */
    public $holding;

    /** @var string */
    protected $item_id;

    /**
     * Item constructor.
     *
     * @param Client $client
     * @param Bib $bib
     * @param Holding $holding
     * @param $item_id
     */
    public function __construct(Client $client, Bib $bib, Holding $holding, $item_id)
    {
        parent::__construct($client);
        $this->bib = $bib;
        $this->holding = $holding;
        $this->item_id = $item_id;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/holdings/{$this->holding->holding_id}/items/{$this->item_id}";
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return isset($data->item_data);
    }

    /**
     * Store data onto object.
     *
     * @param \stdClass $data
     */
    protected function setData(\stdClass $data)
    {
        if (isset($this->bib_data)) {
            $this->bib->init($this->bib_data);
        }
        if (isset($this->holding_data)) {
            $this->holding->init($this->holding_data);
        }
    }

    public function __get($key)
    {
        $this->init();

        if (isset($this->data->item_data->{$key})) {
            return $this->data->item_data->{$key};
        }
        if (isset($this->data->holding_data->{$key})) {
            return $this->data->holding_data->{$key};
        }
        if (isset($this->data->bib_data->{$key})) {
            return $this->data->bib_data->{$key};
        }

        return parent::__get($key);
    }
}
