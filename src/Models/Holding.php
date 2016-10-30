<?php

namespace Scriptotek\Alma\Models;

use Scriptotek\Alma\Client;

class Holding
{
    public $mms_id;
    public $holding_id;
    protected $client;
    protected $data;
    protected $origData;
    protected $dirty = false;

    public function __construct(Client $client, $mms_id, $holding_id)
    {
        $this->mms_id = $mms_id;
        $this->holding_id = $holding_id;
        $this->client = $client;
    }

    public function fetch()
    {
        if (!is_null($this->data)) {
            return;  // we already have the data and won't re-fetch
        }

        $this->data = $this->client->getJSON('/bibs/' . $this->mms_id . '/holdings/' . $this->holding_id);

        // @TODO: Parse

        // $mms_id = $this->data->text('mms_id');
        // if ($mms_id != $this->mms_id) {
        //     throw new \ErrorException('Record mms_id ' . $mms_id . ' does not match requested mms_id ' . $this->mms_id . '.');
        // }

        // $marcRecord = $this->data->first('record')->asXML();
        // $this->_record = MarcRecord::fromString($marcRecord);
    }

}
