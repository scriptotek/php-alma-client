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

        // JSON: Strip away XML declaration to avoid getting
        // "Document labelled UTF-16 but has UTF-8 content" error
        // TODO: Remove once this has been fixed upstream
        // $marcData = trim(preg_replace('/^\<\?xml.*?\?\>/', '', $this->data->anies[0]));

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
        $data = $this->data;
        $data->anies[0] = $this->record->toXML('UTF-8', false, false);
        if (!$this->mms_id) {
            throw new \ErrorException('Cannot save record with no MMS ID');
        }
        return $this->client->put('/bibs/' . $this->mms_id, $data);
    }

    public function __get($key)
    {
        return $this->data->{$key};
    }
}
