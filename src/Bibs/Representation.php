<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single Representation resource.
 */
class Representation extends LazyResource
{
    /* @var string */
    public $representation_id;

    /* @var Bib */
    public $bib;

    /* @var Files */
    public $files;

    public function __construct(Client $client, Bib $bib, $representation_id)
    {
        parent::__construct($client);
        $this->bib = $bib;
        $this->representation_id = $representation_id;
        $this->files = Files::make($this->client, $bib, $this);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/representations/{$this->representation_id}";
    }

    /**
     * Get the files for this representation.
     */
    public function getFiles()
    {
        return $this->files;
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
        return isset($data->delivery_url);
    }
}
