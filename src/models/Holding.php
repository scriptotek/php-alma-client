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

    public function __construct($mms_id, $holding_id, Client $client = null)
    {
        $this->mms_id = $mms_id;
        $this->holding_id = $holding_id;
        $this->client = $client;
    }

}
