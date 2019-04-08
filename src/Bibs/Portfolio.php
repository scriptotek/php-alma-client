<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Electronic\Collection;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single Portfolio resource.
 */
class Portfolio extends LazyResource
{
    /* @var string */
    public $portfolio_id;

    /* @var Bib */
    public $bib;

    public function __construct(Client $client, Bib $bib, $portfolio_id)
    {
        parent::__construct($client);
        $this->bib = $bib;
        $this->portfolio_id = $portfolio_id;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/portfolios/{$this->portfolio_id}";
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
        return isset($data->linking_details);
    }

    public function getElectronicCollection()
    {
        $this->init();

        return new Collection($this->client, $this->data->electronic_collection->id->value);
    }
}
