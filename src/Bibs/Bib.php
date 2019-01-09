<?php

namespace Scriptotek\Alma\Bibs;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Exception\NoLinkedNetworkZoneRecordException;
use Scriptotek\Alma\Model\LazyResource;
use Scriptotek\Alma\Users\Requests;
use Scriptotek\Marc\Record as MarcRecord;
use Scriptotek\Sru\Record as SruRecord;

/**
 * A single Bib resource.
 */
class Bib extends LazyResource
{
    /** @var string */
    public $mms_id;

    /* @var Holdings */
    public $holdings;

    /* @var MarcRecord */
    protected $_marc;

    /** @var Requests */
    public $requests;

    public function __construct(Client $client = null, $mms_id = null)
    {
        parent::__construct($client);
        $this->mms_id = $mms_id;
        $this->holdings = Holdings::make($this->client, $this);
        $this->requests = Requests::make($this->client, $this->url('/requests'));
    }

    /**
     * Get the model data. This API does not support JSON for editing,
     * so we fetch XML instead.
     */
    protected function fetchData()
    {
        return $this->client->getXML($this->url());
    }

    /**
     * Load MARC record onto this Bib object. Chainable method.
     *
     * @param string $xml
     *
     * @return Bib
     */
    public function setMarcRecord($xml)
    {
        $this->_marc = MarcRecord::fromString($xml);
        // Note: do not marc as initialized, since we miss some other data from the Bib record. Oh, Alma :/

        return $this;
    }

    /**
     * Initialize from SRU record without having to fetch the Bib record.
     *
     * @param SruRecord   $record
     * @param Client|null $client
     *
     * @return Bib
     */
    public static function fromSruRecord(SruRecord $record, Client $client = null)
    {
        $record->data->registerXPathNamespace('marc', 'http://www.loc.gov/MARC21/slim');
        $mmsId = $record->data->text('.//marc:controlfield[@tag="001"]');

        return (new self($client, $mmsId))
            ->setMarcRecord($record->data->asXML());
    }

    /**
     * For backwards-compability.
     */
    public function getHoldings()
    {
        return $this->holdings;
    }

    /**
     * Save the MARC record.
     */
    public function save()
    {
        // If initialized from an SRU record, we need to fetch the
        // remaining parts of the Bib record.
        $this->init();

        // Inject the MARC record
        $marcXml = new QuiteSimpleXMLElement($this->_marc->toXML('UTF-8', false, false));
        $this->data->first('record')->replace($marcXml);

        // Serialize
        $newData = $this->data->asXML();

        // Alma doesn't like namespaces
        $newData = str_replace(' xmlns="http://www.loc.gov/MARC21/slim"', '', $newData);

        return $this->client->putXML($this->url(), $newData);
    }

    /**
     * Get the MMS ID of the linked record in network zone.
     */
    public function getNzMmsId()
    {
        // If initialized from an SRU record, we need to fetch the
        // remaining parts of the Bib record.
        $this->init();

        $nz_mms_id = $this->data->text("linked_record_id[@type='NZ']");
        if (!$nz_mms_id) {
            throw new NoLinkedNetworkZoneRecordException("Record $this->mms_id is not linked to a network zone record.");
        }

        return $nz_mms_id;
    }

    /**
     * Get the Bib of the linked record in network zone.
     */
    public function getNzRecord()
    {
        return $this->client->nz->bibs->get($this->getNzMmsId());
    }

    /**
     * Returns the MARC record. Load it if we don't have it yet.
     */
    public function getRecord()
    {
        if (is_null($this->_marc)) {
            $this->init();
        }

        return $this->_marc;
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
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->mms_id}";
    }
}
