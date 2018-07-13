<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\CountableGhostModelList;
use Scriptotek\Alma\IterableResource;

class Holdings extends CountableGhostModelList implements \Countable, \Iterator
{
    use IterableResource;

    /* @var string */
    public $mms_id;

    public function __construct(Client $client, $mms_id)
    {
        parent::__construct($client);
        $this->mms_id = $mms_id;
    }

    public function setData(\stdClass $data)
    {
        $this->resources = array_map(
            function (\stdClass $holding) {
                return Holding::make($this->client, $this->mms_id, $holding->holding_id)
                    ->init($holding);
            },
            $data->holding
        );
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return $data->total_record_count;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->mms_id}/holdings";
    }
}
