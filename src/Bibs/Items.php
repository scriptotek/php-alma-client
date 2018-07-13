<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\GhostResourceList;
use Scriptotek\Alma\IterableResource;

class Items implements GhostResourceList, \Countable
{
    use IterableResource;

    protected $initialized = false;

    protected $client;

    public $mms_id;

    public $holding_id;

    protected $data;

    public function __construct(Client $client, $mms_id = null, $holding_id = null)
    {
        $this->client = $client;
        $this->mms_id = $mms_id;
        $this->holding_id = $holding_id;
    }

    protected function url()
    {
        return "/bibs/{$this->mms_id}/holdings/{$this->holding_id}/items";
    }

    public function init()
    {
        if (!$this->initialized) {
            $response = $this->client->getJSON($this->url());
            $this->data = $response->item;
            $this->initialized = true;
        }

        return $this->data;
    }

    /**
     * Builds an Item object from a response object.
     *
     * @param \stdClass $data
     * @return Item
     */
    protected function buildResource($data) {
        return (new Item($this->client, $this->mms_id, $this->holding_id, $data->pid))
            ->init($data);
    }

    /**
     * Get all the items.
     *
     * @return Item[]
     */
    public function get()
    {
        return array_map([$this, 'buildResource'], $this->init()->data);
    }

    /**
     * Number of items.
     *
     * @return int The number of items as an integer.
     */
    public function count()
    {
        return count($this->init()->data);
    }

    /**
     * Get an Item object from a barcode.
     *
     * @param string $barcode
     *
     * @return Item|null
     */
    public function fromBarcode($barcode)
    {
        $destinationUrl = $this->client->getRedirectLocation('/items', ['item_barcode' => $barcode]);

        // Extract the MMS ID from the redirect target URL.
        // Example: https://api-eu.hosted.exlibrisgroup.com/almaws/v1/bibs/999211285764702204/holdings/22156746440002204/items/23156746430002204
        if (!is_null($destinationUrl) && preg_match('$bibs/([0-9]+)/holdings/([0-9]+)/items/([0-9]+)$', $destinationUrl, $matches)) {
            $mmsId = $matches[1];
            $holdingId = $matches[2];
            $pid = $matches[3];

            return (new Item($this->client, $mmsId, $holdingId, $pid))->init();
        }
    }
}
