<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\ResourceList;

class Bibs extends ResourceList
{
    protected $resourceName = Bib::class;

    /**
     * Get a Bib object from a item barcode.
     *
     * @param string $barcode
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
     * @return Bib
     */
    public function fromHoldingsId($holdings_id)
    {
        $response = $this->client->getXML('/bibs', ['holdings_id' => $holdings_id]);
        $bib_data = $response->first('bib');
        $mms_id = $bib_data->text('mms_id');

        return $this->get($mms_id, null, null, $bib_data);
    }

    /**
     * Get a Bib object from an ISBN value. You must have an SRU client
     * connected to the Alma client (see `Client::setSruClient()`).
     *
     * @param string $isbn
     * @return Bib
     */
    public function fromIsbn($isbn)
    {
        $this->client->assertHasSruClient();

        $record = $this->client->sru->first('alma.isbn="' . $isbn . '"');
        if (is_null($record)) {
            return;
        }

        $mmsId = $record->data->text('//marc:controlfield[@tag="001"]');
        if (empty($mmsId)) {
            $mmsId = $record->data->text('//controlfield[@tag="001"]');
            if (empty($mmsId)) {
                throw new \RuntimeError('SRU returned a record, but it didn\'t contain valid MARC21!');
            }
        }

        return $this->get($mmsId);
    }

    /**
     * Get Bib records from SRU search. You must have an SRU client connected
     * to the Alma client (see `Client::setSruClient()`).
     * Returns a generator that handles continuation under the hood.
     *
     * @param string $cql  The CQL query
     * @param int $batchSize  Number of records to return in each batch.
     * @return \Generator|Bib[]
     */
    public function search($cql, $batchSize = 10)
    {
        $this->client->assertHasSruClient();

        foreach ($this->client->sru->all($cql, $batchSize) as $sruRecord) {
            yield Bib::fromSruRecord($sruRecord, $this->client);
        }
    }
}
