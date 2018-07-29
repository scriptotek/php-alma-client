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

    /* @var Bib */
    public $bib;

    public function __construct(Client $client, Bib $bib)
    {
        parent::__construct($client);
        $this->bib = $bib;
    }

    /**
     * Get resource.
     *
     * @param string $holding_id
     * @return Holding
     */
    public function get($holding_id)
    {
        return Holding::make($this->client, $this->bib, $holding_id);
    }

    public function setData($data)
    {
        $this->resources = array_map(
            function (\stdClass $holding) {
                return Holding::make($this->client, $this->bib, $holding->holding_id)
                    ->init($holding);
            },
            $data->holding
        );
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return $data->total_record_count;
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
