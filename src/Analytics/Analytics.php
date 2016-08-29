<?php

namespace Scriptotek\Alma\Analytics;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Factory;
use Scriptotek\Alma\ResourceList;
use Scriptotek\Alma\ResourceListInterface;

class Analytics extends ResourceList implements ResourceListInterface
{
    protected $resourceName = Report::class;

    public function __construct(Client $client, Factory $factory = null)
    {
        parent::__construct($client, $factory);
    }

    public function getFactoryArgs($element)
    {
        return array($element);
    }
}
