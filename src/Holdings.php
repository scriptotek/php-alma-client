<?php

namespace Scriptotek\Alma;

use Scriptotek\Alma\Models\Holding;

class Holdings extends ResourceList implements ResourceListInterface, \Countable
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

        return [$this->mms_id, $holding_id];
    }

    public function getResources()
    {
        return $this->client->getJSON('/bibs/' . $this->mms_id . '/holdings')->holding;
    }

    public function getResource($id)
    {
        return $this->client->getJSON('/bibs/' . $this->mms_id . '/holdings/' . $id);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->getResources());
    }
}
