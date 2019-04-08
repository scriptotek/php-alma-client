<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

/**
 * Iterable collection of File resources belonging to some Representation resource.
 */
class Files extends LazyResourceList implements \Countable, \Iterator, \ArrayAccess
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /**
     * The Bib this Files list belongs to.
     *
     * @var Bib
     */
    public $bib;

    /**
     * The Representation this Files list belongs to.
     *
     * @var Representation
     */
    public $representation;

    /**
     * Files constructor.
     *
     * @param Client         $client
     * @param Bib            $bib
     * @param Representation $representation
     */
    public function __construct(Client $client, Bib $bib = null, Representation $representation = null)
    {
        parent::__construct($client, 'representation_file');
        $this->bib = $bib;
        $this->representation = $representation;
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     *
     * @return File
     */
    protected function convertToResource($data)
    {
        return File::make($this->client, $this->bib, $this->representation, $data->pid)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/representations/{$this->representation->representation_id}/files";
    }
}
