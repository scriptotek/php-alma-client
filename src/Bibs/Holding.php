<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\GhostModel;
use Scriptotek\Marc\Record as MarcRecord;

class Holding extends GhostModel
{
    /* @var string */
    public $mms_id;

    /* @var string */
    public $holding_id;

    /* @var Bib */
    public $bib;

    /* @var Items */
    public $items;

    /* @var MarcRecord */
    protected $_marc;

    public function __construct(Client $client, $mms_id, $holding_id)
    {
        parent::__construct($client);
        $this->mms_id = $mms_id;
        $this->holding_id = $holding_id;
        $this->items = Items::make($this->client, $mms_id, $holding_id);
        $this->bib = Bib::make($this->client, $mms_id);
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return isset($data->anies);
    }

    /**
     * Store data onto object.
     *
     * @param \stdClass $data
     */
    protected function setData(\stdClass $data)
    {
        $this->_marc = MarcRecord::fromString($data->anies[0]);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->mms_id}/holdings/{$this->holding_id}";
    }

    /**
     * Get the MARC record.
     */
    public function getRecord()
    {
        return $this->init()->_marc;
    }

    /**
     * Get the items for this holding.
     */
    public function getItems()
    {
        return $this->items;
    }
}
