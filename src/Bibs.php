<?php

namespace Scriptotek\Alma;

use Scriptotek\Alma\Models\Bib;

class Bibs extends ResourceList implements ResourceListInterface
{

    protected $resourceName = 'Bib';

    public function __construct(Client $client, Factory $factory = null)
    {
        parent::__construct($client, $factory);
    }

    public function getFactoryArgs($element)
    {
        return array($element);
    }

    public function getResources($force = false)
    {
        // No endpoint available...
        throw new \ErrorException('Action not supported by Alma');
    }

    public function getResource($id)
    {
        return $id;
    }

}
