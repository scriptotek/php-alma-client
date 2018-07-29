<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\Model\LazyResource;
use Scriptotek\Alma\Users\Loan;
use Scriptotek\Alma\Users\User;

class Item extends LazyResource
{
    /** @var Bib */
    public $bib;

    /** @var Holding */
    public $holding;

    /** @var string */
    protected $item_id;

    /**
     * Item constructor.
     *
     * @param Client $client
     * @param Bib $bib
     * @param Holding $holding
     * @param $item_id
     */
    public function __construct(Client $client, Bib $bib, Holding $holding, $item_id)
    {
        parent::__construct($client);
        $this->bib = $bib;
        $this->holding = $holding;
        $this->item_id = $item_id;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/holdings/{$this->holding->holding_id}/items/{$this->item_id}";
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return isset($data->item_data);
    }

    /**
     * Store data onto object.
     *
     * @param \stdClass $data
     */
    protected function setData($data)
    {
        if (isset($this->bib_data)) {
            $this->bib->init($this->bib_data);
        }
        if (isset($this->holding_data)) {
            $this->holding->init($this->holding_data);
        }
    }

    /**
     * Create a new loan.
     *
     * @param User $user
     * @param Library $library
     * @param string $circ_desk
     * @return Loan
     * @throws \Scriptotek\Alma\Exception\RequestFailed
     */
    public function checkOut(User $user, Library $library, $circ_desk = 'DEFAULT_CIRC_DESK')
    {
        $postData = [
            'library' => ['value' => $library->code],
            'circ_desk' => ['value' => $circ_desk],
        ];

        $data = $this->client->postJSON(
            $this->url('/loans', ['user_id' => $user->id]),
            $postData
        );

        return Loan::make($this->client, $user, $data->loan_id)
            ->init($data);
    }

    /**
     * Perform scan-in on item.
     *
     * @param Library $library
     * @param string $circ_desk
     * @param array $params
     * @return ScanInResponse
     * @throws \Scriptotek\Alma\Exception\RequestFailed
     */
    public function scanIn(Library $library, $circ_desk = 'DEFAULT_CIRC_DESK', $params = [])
    {
        $params['op'] = 'scan';
        $params['library'] = $library->code;
        $params['circ_desk'] = $circ_desk;

        $data = $this->client->postJSON($this->url('', $params));

        return ScanInResponse::make($this->client, $data);
    }

    /**
     * Get the current loan as a Loan object, or null if the item is not loaned out.
     *
     * @returns Loan|null
     */
    public function loan()
    {
        $data = $this->client->getJSON($this->url('/loans'));

        if ($data->total_record_count == 1) {
            return Loan::make(
                $this->client,
                User::make($this->client, $data->item_loan[0]->user_id),
                $data->item_loan[0]->loan_id
            )->init($data->item_loan[0]);
        }

        return null;
    }

    public function __get($key)
    {
        $this->init();

        if (isset($this->data->item_data->{$key})) {
            return $this->data->item_data->{$key};
        }
        if (isset($this->data->holding_data->{$key})) {
            return $this->data->holding_data->{$key};
        }
        if (isset($this->data->bib_data->{$key})) {
            return $this->data->bib_data->{$key};
        }

        return parent::__get($key);
    }
}
