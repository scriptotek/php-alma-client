<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Marc\Record as MarcRecord;

class Holding
{
    protected $client;
    public $mms_id;
    public $holding_id;
    protected $_marc;
    protected $_items;

    public function __construct(Client $client, $mms_id, $holding_id)
    {
        $this->client = $client;
        $this->mms_id = $mms_id;
        $this->holding_id = $holding_id;
    }

    /**
     * Returns the MARC record.
     */
    public function getRecord()
    {
        if (!isset($this->_marc)) {
            $data = $this->client->getXML('/bibs/' . $this->mms_id . '/holdings/' . $this->holding_id);
            $marcRecord = $data->first('record')->asXML();
            $this->_marc = MarcRecord::fromString($marcRecord);
        }

        return $this->_marc;
    }

    public function getItems()
    {
        if (!isset($this->_items)) {
            $data = $this->client->getJSON('/bibs/' . $this->mms_id . '/holdings/' . $this->holding_id . '/items');

            $this->_items = array_map(function ($data) {
                return new Item($data);
            }, $data->item);
        }

        return $this->_items;
    }

    public function __get($key)
    {
        if ($key == 'record') {
            return $this->getRecord();
        }
        if ($key == 'items') {
            return $this->getItems();
        }
    }
}
