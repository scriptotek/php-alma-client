<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

class Portfolios extends LazyResourceList implements \Countable, \Iterator, \ArrayAccess
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /**
     * The Bib this Portfolios list belongs to.
     *
     * @var Bib
     */
    public $bib;

    /**
     * Portfolios constructor.
     *
     * @param Client $client
     * @param Bib    $bib
     */
    public function __construct(Client $client, Bib $bib)
    {
        parent::__construct($client, 'portfolio');
        $this->bib = $bib;
    }

    /**
     * Get a single portfolio record by id.
     *
     * @param string $id
     *
     * @return Portfolio
     */
    public function get($id)
    {
        return Portfolio::make($this->client, $this->bib, $id);
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     *
     * @return Portfolio
     */
    protected function convertToResource($data)
    {
        return Portfolio::make($this->client, $this->bib, $data->id)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/portfolios";
    }
}
