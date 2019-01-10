<?php

namespace Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

class File extends LazyResource
{
    /** @var Bib */
    public $bib;

    /** @var Representation */
    public $representation;

    /** @var Requests */
    public $requests;

    /** @var string */
    public $file_id;

    /**
     * File constructor.
     *
     * @param Client  $client
     * @param Bib     $bib
     * @param Representation $representation
     * @param $file_id
     */
    public function __construct(Client $client, Bib $bib, Representation $representation, $file_id)
    {
        parent::__construct($client);
        $this->bib = $bib;
        $this->representation = $representation;
        $this->file_id = $file_id;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/bibs/{$this->bib->mms_id}/representations/{$this->representation->representation_id}/files/{$this->file_id}";
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
        return isset($data->path);
    }
}
