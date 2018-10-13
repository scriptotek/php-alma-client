<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\Model;

/**
 * Response from a scan-in operation of some Item resource.
 */
class ScanInResponse extends Model
{
    protected $data;

    /**
     * ScanInResponse constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client, $data)
    {
        parent::__construct($client, $data);
    }

    /**
     * Get the scan-in response message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->data->additional_info;
    }

    /**
     * Get the scanned-in item.
     *
     * @return Item
     */
    public function getItem()
    {
        $bib = Bib::make($this->client, $this->data->bib_data->mms_id);
        $holding = Holding::make($this->client, $bib, $this->data->holding_data->holding_id);

        return Item::make(
            $this->client,
            $bib,
            $holding,
            $this->data->item_data->pid
        )->init($this->data->item_data);
    }

    /**
     * Get the Holding object for the scanned-in item.
     *
     * @return Holding
     */
    public function getHolding()
    {
        $bib = Bib::make($this->client, $this->data->bib_data->mms_id);

        return Holding::make(
            $this->client,
            $bib,
            $this->data->holding_data->holding_id
        )->init($this->data->holding_data);
    }

    /**
     * Get the Bib object for the scanned-in item.
     *
     * @return Bib
     */
    public function getBib()
    {
        return Bib::make(
            $this->client,
            $this->data->bib_data->mms_id
        )->init($this->data->bib_data);
    }
}
