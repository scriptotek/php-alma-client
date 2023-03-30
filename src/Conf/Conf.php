<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Conf\Jobs;
use Scriptotek\Alma\Conf\CodeTables;

class Conf
{
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->libraries = new Libraries($client);
        $this->jobs = new Jobs($client);
        $this->codetables = new CodeTables($client);
    }
}
