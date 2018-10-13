<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

/**
 * Non-iterable collection of Bib resources.
 */
class Bibs implements \ArrayAccess
{
    use ReadOnlyArrayAccess;

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get a Bib object.
     *
     * @param string $mms_id
     * @param array  $expand Expand the bibliographic record with additional information.
     *
     * @return Bib
     */
    public function get($mms_id, $expand = null)
    {
        $params = ['expand' => $expand];

        return Bib::make($this->client, $mms_id)
            ->setParams($params);
    }

    /**
     * Get a Bib object from a item barcode.
     *
     * @param string $barcode
     *
     * @return Bib
     */
    public function fromBarcode($barcode)
    {
        $destinationUrl = $this->client->getRedirectLocation('/items', ['item_barcode' => $barcode]);

        // Extract the MMS ID from the redirect target URL.
        // Example: https://api-eu.hosted.exlibrisgroup.com/almaws/v1/bibs/999211285764702204/holdings/22156746440002204/items/23156746430002204
        if (!is_null($destinationUrl) && preg_match('$bibs/([0-9]+)/holdings/([0-9]+)/items/([0-9]+)$', $destinationUrl, $matches)) {
            $mmsId = $matches[1];

            return $this->get($mmsId);
        }
    }

    /**
     * Get a Bib object from a holdings ID.
     *
     * @param string $holdings_id
     *
     * @return Bib
     */
    public function fromHoldingsId($holdings_id)
    {
        $data = $this->client->getXML('/bibs', ['holdings_id' => $holdings_id]);

        return $this->get($data->text('bib/mms_id'))
            ->init($data->first('bib'));
    }

    /**
     * Get Bib records from SRU search. You must have an SRU client connected
     * to the Alma client (see `Client::setSruClient()`).
     * Returns a generator that handles continuation under the hood.
     *
     * @param string $cql       The CQL query
     * @param int    $batchSize Number of records to return in each batch.
     *
     * @return \Generator|Bib[]
     */
    public function search($cql, $batchSize = 10)
    {
        $this->client->assertHasSruClient();

        foreach ($this->client->sru->all($cql, $batchSize) as $sruRecord) {
            yield Bib::fromSruRecord($sruRecord, $this->client);
        }
    }

    /**
     * Returns the first result from a SRU search or null if no results.
     *
     * @param string $cql
     *
     * @return Bib
     */
    public function findOne($cql)
    {
        return $this->search($cql, 1)->current();
    }

    /**
     * Get a Bib object from an ISBN value. Returns null if no Bib record found.
     *
     * @param string $isbn
     *
     * @return Bib
     */
    public function fromIsbn($isbn)
    {
        return $this->findOne('alma.isbn="' . $isbn . '"');
    }
}
