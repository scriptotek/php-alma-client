<?php

namespace Scriptotek\Alma;

use Scriptotek\Alma\Models\Bib;

class Bibs extends ResourceList implements ResourceListInterface
{

    protected $resourceName = 'Bib';

    public function __construct(Client $client, Factory $factory = null)
    {
        parent::__construct($client, $factory);
    }

    public function getFactoryArgs($element)
    {
        return array($element);
    }

    public function fromBarcode($barcode)
    {
        $destinationUrl = $this->client->getRedirectLocation('/items', ['item_barcode' => $barcode]);

        // Example: https://api-eu.hosted.exlibrisgroup.com/almaws/v1/bibs/999211285764702204/holdings/22156746440002204/items/23156746430002204
        if (!is_null($destinationUrl) && preg_match('$bibs/([0-9]+)/holdings/([0-9]+)/items/([0-9]+)$', $destinationUrl, $matches)) {
            $mmsId = $matches[1];
            return $this->getResource($mmsId);
        }

        return null;
    }

    public function fromIsbn($isbn)
    {
        $record = $this->client->sru->first('alma.isbn="' . $isbn . '"');
        if (is_null($record)) {
            return null;
        }
        $mmsId = $record->data->text('//controlfield[@tag="001"]');

        return $this->getResource($mmsId);
    }

    public function search($cql, $batchSize=10)
    {
        foreach ($this->client->sru->all($cql, $batchSize) as $sruRecord) {
            yield Bib::fromSruRecord($sruRecord, $this->client);
        }
    }
}
