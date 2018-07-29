<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Exception\NoLinkedNetworkZoneRecordException;
use Scriptotek\Alma\Model\LazyResource;
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

    public function __construct(Client $client = null, $mms_id = null)
    {
        parent::__construct($client);
        $this->mms_id = $mms_id;
        $this->holdings = Holdings::make($this->client, $this);
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
        // Workaround for a long-standing API-bug
        $xml = str_replace('UTF-16', 'UTF-8', $xml);

        $this->_marc = MarcRecord::fromString($xml);
        // Note: do not marc as initialized, since we miss some other data from the Bib record. Oh, Alma :/

        return $this;
    }

    /**
     * Initialize from SRU record without having to fetch the Bib record.
     * @param SruRecord $record
     * @param Client|null $client
     * @return Bib
     */
    public static function fromSruRecord(SruRecord $record, Client $client = null)
    {
        $record->data->registerXPathNamespace('marc', 'http://www.loc.gov/MARC21/slim');
        $mmsId = $record->data->text('.//controlfield[@tag="001"]');

        return (new self($client, $mmsId))
            ->setMarcRecord($record->data->asXML());
    }

    public function getHoldings()
    {
        return $this->holdings;
    }

    public function save()
    {
        // If initialized from an SRU record, we need to fetch the
        // remaining parts of the Bib record.
        $this->init();

        // Serialize the MARC record
        $data = $this->_marc->toXML('UTF-8', false, false);

        // but wait, Alma hates namespaces, so we have to remove them...
        $data = str_replace(' xmlns="http://www.loc.gov/MARC21/slim"', '', $data);

        $this->data->anies = [$data];

        return $this->client->putJSON('/bibs/' . $this->mms_id, $data);
    }

    /**
     * Get the MMS ID of the linked record in network zone.
     */
    public function getNzMmsId()
    {
        // If initialized from an SRU record, we need to fetch the
        // remaining parts of the Bib record.
        $this->init();

        // @TODO: What if record is also linked to CZ? Probably an array is returned.
        $nz_mms_id = $this->data->linked_record_id->value;
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
     * Store data onto object.
     *
     * @param \stdClass $data
     */
    protected function setData($data)
    {
        $this->setMarcRecord($data->anies[0]);
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
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->mms_id}";
    }
}
