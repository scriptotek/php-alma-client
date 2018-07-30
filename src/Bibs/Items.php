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

    /**
     * The Bib this Items list belongs to.
     *
     * @var Bib
     */
    public $bib;

    /**
     * The Holding this Items list belongs to.
     *
     * @var Holding
     */
    public $holding;

    /**
     * Items constructor.
     *
     * @param Client $client
     * @param Bib $bib
     * @param Holding $holding
     */
    public function __construct(Client $client, Bib $bib = null, Holding $holding = null)
    {
        parent::__construct($client, 'item');
        $this->bib = $bib;
        $this->holding = $holding;
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     * @return Item
     */
    protected function convertToResource($data)
    {
        return Item::make($this->client, $this->bib, $this->holding, $data->item_data->pid)
            ->init($data);
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
}
