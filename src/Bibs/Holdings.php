<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

/**
 * Iterable collection of Holding resources belonging to some Bib resource.
 */
class Holdings extends LazyResourceList implements \Countable, \Iterator, \ArrayAccess
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /**
     * The Bib this Holdings list belongs to.
     *
     * @var Bib
     */
    public $bib;

    /**
     * Holdings constructor.
     *
     * @param Client $client
     * @param Bib $bib
     */
    public function __construct(Client $client, Bib $bib)
    {
        parent::__construct($client, 'holding');
        $this->bib = $bib;
    }

    /**
     * Get a single holding record by id.
     *
     * @param string $holding_id
     * @return Holding
     */
    public function get($holding_id)
    {
        return Holding::make($this->client, $this->bib, $holding_id);
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     * @return Holding
     */
    protected function convertToResource($data)
    {
        return Holding::make($this->client, $this->bib, $data->holding_id)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/holdings";
    }
}
