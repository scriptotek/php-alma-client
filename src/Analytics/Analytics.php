<?php

namespace Scriptotek\Alma\Analytics;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

/**
 * Non-iterable collection of Report resources.
 */
class Analytics implements \ArrayAccess
{
    use ReadOnlyArrayAccess;

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
