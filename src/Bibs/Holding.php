<?php

namespace Scriptotek\Alma\Bibs;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;
use Scriptotek\Marc\Record as MarcRecord;

/**
 * A single Holding resource.
 */
class Holding extends LazyResource
{
    /* @var string */
    public $holding_id;

    /* @var Bib */
    public $bib;

    /* @var Items */
    public $items;

    /* @var MarcRecord */
    protected $_marc;

    public function __construct(Client $client, Bib $bib, $holding_id)
    {
        parent::__construct($client);
        $this->bib = $bib;
        $this->holding_id = $holding_id;
        $this->items = Items::make($this->client, $bib, $this);
    }

    /**
     * Get the model data.
     */
    protected function fetchData()
    {
        return $this->client->getXML($this->url());
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param $data
     *
     * @return bool
     */
    protected function isInitialized($data)
    {
        return is_a($data, QuiteSimpleXMLElement::class) && $data->has('record');
    }

    /**
     * Load MARC record onto this Bib object. Chainable method.
     *
     * @param string $xml
     *
     * @return Holding
     */
    public function setMarcRecord($xml)
    {
        $this->_marc = MarcRecord::fromString($xml);

        return $this;
    }

    /**
     * Called when data is available to be processed.
     *
     * @param mixed $data
     */
    protected function onData($data)
    {
        $this->setMarcRecord($data->first('record')->asXML());
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/holdings/{$this->holding_id}";
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
