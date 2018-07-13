<?php

namespace Scriptotek\Alma\Analytics;

use Scriptotek\Alma\Client;

class Analytics
{
    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function get(...$args)
    {
        return new Report($this->client, ...$args);
    }
}
