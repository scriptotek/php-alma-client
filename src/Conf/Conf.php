<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Conf\Jobs;

class Conf
{
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->libraries = new Libraries($client);
        $this->jobs = new Jobs($client);
    }
}
