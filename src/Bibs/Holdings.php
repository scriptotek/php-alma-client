<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\IterableResource;
use Scriptotek\Alma\ResourceList;

class Holdings extends ResourceList implements \Countable, \Iterator
{
    use IterableResource;

    protected $resourceName = Holding::class;

    protected $mms_id;

    public function __construct(Client $client, $mms_id)
    {
        parent::__construct($client);
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
