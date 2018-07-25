<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\GhostModel;

class Library extends GhostModel
{
    /** @var string */
    public $code;

    /** @var Locations */
    public $locations;

    /**
     * Library constructor.
     *
     * @param Client $client
     * @param string $code
     */
    public function __construct(Client $client, $code)
    {
        parent::__construct($client);
        $this->code = $code;
        $this->locations = Locations::make($this->client, $this);
    }

    /**
     * Check if we have the full representation of our data object.
     * For libraries, it seems like we get the same data in the list
     * response as in the single-item response, so we just check some
     * random element.
     *
     * @param \stdClass $data
     * @return boolean
     */
    protected function isInitialized($data)
    {
        return isset($data->path);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/conf/libraries/{$this->code}";
    }
}
