<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\CountableGhostModelList;
use Scriptotek\Alma\IterableResource;

class Items extends CountableGhostModelList implements \Countable, \Iterator
{
    use IterableResource;

    /* @var string */
    public $mms_id;

    /* @var string */
    public $holding_id;

    public function __construct(Client $client, $mms_id = null, $holding_id = null)
    {
        parent::__construct($client);
        $this->mms_id = $mms_id;
        $this->holding_id = $holding_id;
    }

    public function setData(\stdClass $data)
    {
        $this->resources = array_map(
            function (\stdClass $item) {
                return Item::make($this->client, $this->mms_id, $this->holding_id, $item->item_data->pid)
                    ->init($item);
            },
            $data->item
        );
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
            $mms_id = $matches[1];
            $holding_id = $matches[2];
            $item_id = $matches[3];

            return Item::make($this->client, $mms_id, $holding_id, $item_id);
        }

        return null;
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return isset($data->total_record_count);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->mms_id}/holdings/{$this->holding_id}/items";
    }
}
