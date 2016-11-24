<?php

namespace Scriptotek\Alma\Bibs;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Exception\NoLinkedNetworkZoneRecordException;
use Scriptotek\Marc\Record as MarcRecord;
use Scriptotek\Sru\Record as SruRecord;

class Bib
{
    public $mms_id;

    /** @var Client */
    protected $client;

    /** @var QuiteSimpleXMLElement */
    protected $bib_data = null;

    /* @var MarcRecord */
    protected $marc_data = null;

    protected $_holdings = null;

    public function __construct(Client $client = null, $mms_id = null, MarcRecord $marc_data = null, QuiteSimpleXMLElement $bib_data = null)
    {
        $this->mms_id = $mms_id;
        $this->client = $client;
        $this->marc_data = $marc_data;
        $this->bib_data = $bib_data;
        $this->setMarcDataFromBibData();
    }

    /**
     * Initialize from SRU record without having to fetch the Bib record.
     */
    public static function fromSruRecord(SruRecord $record, Client $client = null)
    {
        $record->data->registerXPathNamespace('marc', 'http://www.loc.gov/MARC21/slim');
        $marcRecord = MarcRecord::fromString($record->data->asXML());

        return new self($client, strval($marcRecord->id), $marcRecord);
    }

    /* Lazy load */
    protected function load()
    {
        if (!is_null($this->bib_data)) {
            return;  // we already have the data and won't re-fetch
        }

        // If we already have the MARC record (from SRU), we should make a copy
        // in case the user has made edits to it.
        $marcRecord = isset($this->marc_data)
            ? new QuiteSimpleXMLElement($this->marc_data->toXML('UTF-8', false, false))
            : null ;

        $options = [];
        $this->bib_data = $this->client->getXML('/bibs/' . $this->mms_id, $options);

        $mms_id = $this->bib_data->text('mms_id');
        if ($mms_id != $this->mms_id) {
            throw new \ErrorException('Record mms_id ' . $mms_id . ' does not match requested mms_id ' . $this->mms_id . '.');
        }

        if (is_null($marcRecord)) {
            $this->setMarcDataFromBibData();
        } else {
            $this->bib_data->first('record')->replace($marcRecord);
        }
    }

    protected function setMarcDataFromBibData()
    {
        if (!is_null($this->bib_data)) {
            $marcRecord = $this->bib_data->first('record')->asXML();
            $this->marc_data = MarcRecord::fromString($marcRecord);
        }
    }

    public function getHolding($holding_id)
    {
        return new Holding($this->client, $this->mms_id, $holding_id);
    }

    public function getHoldings()
    {
        if (is_null($this->_holdings)) {
            $this->_holdings = new Holdings($this->client, $this->mms_id);
        }

        return $this->_holdings;
    }

    public function save()
    {
        // If initialized from an SRU record, we need to fetch the
        // remaining parts of the Bib record.
        $this->load();

        // Serialize
        $newData = $this->bib_data->asXML();

        // Alma doesn't like namespaces
        $newData = str_replace(' xmlns="http://www.loc.gov/MARC21/slim"', '', $newData);

        return $this->client->putXML('/bibs/' . $this->mms_id, $newData);
    }

    public function getXml()
    {
        if (is_null($this->bib_data)) {
            $this->load();
        }
        return $this->bib_data->asXML();
    }

    public function getNzRecord()
    {
        // If initialized from an SRU record, we need to fetch the
        // remaining parts of the Bib record.
        $this->load();

        $nz_mms_id = $this->bib_data->text('linked_record_id[@type="NZ"]');
        if (!$nz_mms_id) {
            throw new NoLinkedNetworkZoneRecordException("Record $this->mms_id is not linked to a network zone record.");
        }

        return $this->client->nz->bibs->get($nz_mms_id);
    }


    /**
     * Returns the MARC record
     */
    public function getRecord()
    {
        if (is_null($this->marc_data)) {
            $this->load();
        }

        return $this->marc_data;
    }

    public function __get($key)
    {
        if ($key == 'record') {
            return $this->getRecord();
        }
        if ($key == 'holdings') {
            return $this->getHoldings();
        }
        $this->load();
        return $this->bib_data->text($key);
    }
}
