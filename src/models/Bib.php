<?php

namespace Scriptotek\Alma\Models;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
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
        $newRecord = new QuiteSimpleXMLElement($this->record->toXML('UTF-8', false, false));
        $this->data->first('record')->replace($newRecord);
        $newData = $this->data->asXML();

        // Alma doesn't like namespaces
        $newData = str_replace(' xmlns="http://www.loc.gov/MARC21/slim"', '', $newData);

        return $this->client->putXML('/bibs/' . $this->mms_id, $newData);
    }

    public function __get($key)
    {
        return $this->data->{$key};
    }
}
