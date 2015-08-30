<?php

namespace Scriptotek\Alma;

use Scriptotek\Alma\Models\Holding;

class Holdings extends ResourceList implements ResourceListInterface
{

    protected $resourceName = 'Holding';

    protected $mms_id;

    public function __construct($mms_id, Client $client, Factory $factory = null)
    {
        parent::__construct($client, $factory);
        $this->mms_id = $mms_id;
    }

    public function getFactoryArgs($element)
    {
        $holding_id = $element->holding_id;
        return array($this->mms_id, $holding_id);
    }

    public function getResources()
    {
        return $this->client->getJSON('/bibs/' . $this->mms_id . '/holdings')->holding;
    }

    public function getResource($id)
    {
        return $this->client->getJSON('/bibs/' . $this->mms_id . '/holdings/' . $id);
    }

}
