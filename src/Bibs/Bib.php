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
    protected $data = null;

    /* @var MarcRecord */
    protected $_marc = null;

    protected $_holdings;

    public function __construct(Client $client = null, $mms_id = null, MarcRecord $marc = null)
    {
        $this->mms_id = $mms_id;
        $this->client = $client;
        $this->_marc = $marc;
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

    public function fetch()
    {
        if (!is_null($this->data)) {
            return;  // we already have the data and won't re-fetch
        }

        $this->data = $this->client->getXML('/bibs/' . $this->mms_id);

        $mms_id = $this->data->text('mms_id');
        if ($mms_id != $this->mms_id) {
            throw new \ErrorException('Record mms_id ' . $mms_id . ' does not match requested mms_id ' . $this->mms_id . '.');
        }

        $marcRecord = $this->data->first('record')->asXML();
        $this->_marc = MarcRecord::fromString($marcRecord);
    }

    public function holdings()
    {
        if (!isset($this->_holdings)) {
            $this->_holdings = new Holdings($this->client, $this->mms_id);
        }

        return $this->_holdings;
    }

    public function save(MarcRecord $rec)
    {
        // If initialized from an SRU record, we need to fetch the
        // remaining parts of the Bib record.
        $this->fetch();

        // Replace the MARC record
        $newRecord = new QuiteSimpleXMLElement($rec->toXML('UTF-8', false, false));
        $this->data->first('record')->replace($newRecord);

        // Serialize
        $newData = $this->data->asXML();

        // Alma doesn't like namespaces
        $newData = str_replace(' xmlns="http://www.loc.gov/MARC21/slim"', '', $newData);

        return $this->client->putXML('/bibs/' . $this->mms_id, $newData);
    }

    public function getNzRecord()
    {
        // If initialized from an SRU record, we need to fetch the
        // remaining parts of the Bib record.
        $this->fetch();

        $nz_mms_id = $this->data->text('linked_record_id[@type="NZ"]');
        if (!$nz_mms_id) {
            throw new NoLinkedNetworkZoneRecordException("Record $this->mms_id is not linked to a network zone record.");
        }

        return $this->client->nz->bibs->get($nz_mms_id);
    }


    public function getMarc()
    {
        return $this->_marc;
    }

    public function __get($key)
    {
        if ($key == 'marc') {
            return $this->getMarc();
        }
        if (!is_null($this->data)) {
            return $this->data->text($key);
        }
    }
}
