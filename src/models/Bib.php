<?php

namespace Scriptotek\Alma\Models;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Exception\NoLinkedNetworkZoneRecordException;
use Scriptotek\Alma\Holdings;
use Scriptotek\Marc\Record;

class Bib
{
    public $mms_id;

    /** @var Client */
    protected $client;

    /** @var QuiteSimpleXMLElement */
    protected $data;

    protected $_holdings;

    protected $dirty = false;

    public function __construct($mms_id = null, Client $client = null)
    {
        $this->mms_id = $mms_id;
        $this->client = $client;
    }

    public function fetch()
    {
        $this->data = $this->client->getXML('/bibs/' . $this->mms_id);

        $mms_id = $this->data->first('mms_id');
        if ($mms_id != $this->mms_id) {
            throw new \ErrorException('Record mms_id ' . $mms_id . ' does not match requested mms_id ' . $this->mms_id . '.');
        }

        $marcRecord = $this->data->first('record')->asXML();
        $this->record = Record::fromString($marcRecord);
    }

    public function holdings()
    {
        if (!isset($this->_holdings)) {
            $this->_holdings = new Holdings($this->mms_id, $this->client);
        }
        return $this->_holdings;
    }

    // public function isDirty()
    // {
    //     return strcmp(json_encode($this->data), json_encode($this->origData)) != 0;
    // }

    public function save()
    {
        $newRecord = new QuiteSimpleXMLElement($this->record->toXML('UTF-8', false, false));
        $this->data->first('record')->replace($newRecord);
        $newData = $this->data->asXML();

        // Alma doesn't like namespaces
        $newData = str_replace(' xmlns="http://www.loc.gov/MARC21/slim"', '', $newData);

        return $this->client->putXML('/bibs/' . $this->mms_id, $newData);
    }

    public function getNzRecord()
    {
        $nz_mms_id = $this->data->text('linked_record_id[@type="NZ"]');
        if (!$nz_mms_id) {
            throw new NoLinkedNetworkZoneRecordException("Record $this->mms_id is not linked to a network zone record.");
        }
        return $this->client->nz->bibs[$nz_mms_id];
    }

    public function __get($key)
    {
        return $this->data->text($key);
    }
}
