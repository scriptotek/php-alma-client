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
    protected $data;

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
        if (!isset($this->data)) {
            $this->data = $this->client->getJSON('/bibs/' . $this->mms_id . '/holdings')->holding;
        }

        return $this->data;
    }

    /**
     * Number of holdings.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The number of holdings as an integer.
     */
    public function count()
    {
        return count($this->getResources());
    }
}
