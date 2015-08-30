<?php

namespace Scriptotek\Alma\Models;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Holdings;
use Scriptotek\Marc\Record;

class Bib
{
    public $mms_id;
    protected $client;
    protected $data;
    protected $origData;
    protected $_holdings;
    protected $dirty = false;

    public function __construct($mms_id = null, Client $client = null)
    {
        $this->mms_id = $mms_id;
        $this->client = $client;
    }

    public function holdings()
    {
        if (!isset($this->_holdings)) {
            $this->_holdings = new Holdings($this->mms_id, $this->client);
        }
        return $this->_holdings;
    }

    public function isDirty()
    {
        return strcmp($this->data->asXML(), $this->origData->asXML()) != 0;
    }

    public function save()
    {
        if (!$this->isDirty()) {
            return;
        }
        if (!$this->mms_id) {
            throw new \ErrorException('Cannot save record with no MMS ID');
        }
        $data = $this->data;
        return $this->client->put('/bibs/' . $this->mms_id, $data);
    }

    public function __set($key, $value)
    {
        print " { $key } ";
        $this->dirty = true;

        if ($key == 'record') {
            return simplexml_load_string($value->toXML());
        } else {
            $this->data->{$key} = $value;
        }

    }

    // public function getRecord()
    // {
    //     return Record::from($this->data);
    // }

    // public function setRecord($record)
    // {
    //     $this->data = $record->toXML();
    // }

    public function __get($key)
    {
        if (!isset($this->data)) {
            $this->data = $this->client->get('/bibs/' . $this->mms_id);
            $this->origData = clone $this->data;
        }

        if ($key == 'record') {
            return Record::fromString($this->data->{$key}->asXML());
        }

        return $this->data->{$key};
    }
}
