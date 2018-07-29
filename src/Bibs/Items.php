<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Model\ReadOnlyArrayAccess;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\IterableCollection;

/**
 * Iterable collection of Item resources belonging to some Holding resource.
 */
class Items extends LazyResourceList implements \Countable, \Iterator, \ArrayAccess
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /* @var Bib */
    public $bib;

    /* @var Holding*/
    public $holding;

    public function __construct(Client $client, Bib $bib = null, Holding $holding = null)
    {
        parent::__construct($client);
        $this->bib = $bib;
        $this->holding = $holding;
    }

    public function setData($data)
    {
        $this->resources = array_map(
            function (\stdClass $item) {
                return Item::make($this->client, $this->bib, $this->holding, $item->item_data->pid)
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

            $bib = Bib::make($this->client, $mms_id);
            $holding = Holding::make($this->client, $bib, $holding_id);

            return Item::make($this->client, $bib, $holding, $item_id);
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
        return "/bibs/{$this->bib->mms_id}/holdings/{$this->holding->holding_id}/items";
    }
}
