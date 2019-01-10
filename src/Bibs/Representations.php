<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

class Representations extends LazyResourceList implements \Countable, \Iterator, \ArrayAccess
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /**
     * The Bib this Representations list belongs to.
     *
     * @var Bib
     */
    public $bib;

    /**
     * Representations constructor.
     *
     * @param Client $client
     * @param Bib    $bib
     */
    public function __construct(Client $client, Bib $bib)
    {
        parent::__construct($client, 'representation');
        $this->bib = $bib;
    }

    /**
     * Get a single representation record by id.
     *
     * @param string $id
     *
     * @return Representation
     */
    public function get($id)
    {
        return Representation::make($this->client, $this->bib, $id);
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     *
     * @return Representation
     */
    protected function convertToResource($data)
    {
        return Representation::make($this->client, $this->bib, $data->id)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/representations";
    }
}
