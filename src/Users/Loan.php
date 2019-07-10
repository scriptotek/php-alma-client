<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single Loan resource.
 */
class Loan extends LazyResource
{
    /* @var User */
    protected $user;

    /* @var string */
    protected $loan_id;

    public function __construct(Client $client, User $user, $loan_id)
    {
        parent::__construct($client);
        $this->user = $user;
        $this->loan_id = $loan_id;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return sprintf('/users/%s/loans/%s', rawurlencode($this->user->id), $this->loan_id);
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     *
     * @return bool
     */
    protected function isInitialized($data)
    {
        return isset($data->loan_id);
    }

    /**
     * Get the Item on loan. Since the response from the loan(s) API does not
     * include the `holding_id` and `item_pid`, we cannot initiate an Item object
     * directly, so we have to lookup the barcode instead.
     *
     * @see https://developers.exlibrisgroup.com/discussions#!/forum/posts/list/1397.page
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->client->items->fromBarcode($this->item_barcode);
    }
}
