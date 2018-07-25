<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\ArrayAccessResource;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\CountableGhostModelList;
use Scriptotek\Alma\IterableResource;

class Locations extends CountableGhostModelList implements \ArrayAccess, \Countable, \Iterator
{
    use ArrayAccessResource;
    use IterableResource;

    /** @var Library */
    protected $library;

    /**
     * Locations constructor.
     *
     * @param Client $client
     * @param Library $library
     */
    public function __construct(Client $client, Library $library)
    {
        parent::__construct($client);
        $this->library = $library;
    }

    /**
     * Get resource.
     *
     * @param string $code
     * @return Location
     */
    public function get($code)
    {
        return Location::make($this->client, $this->library, $code);
    }

    protected function setData(\stdClass $data)
    {
        $this->resources = array_map(
            function (\stdClass $location) {
                return Location::make($this->client, $this->library, $location->code)
                    ->init($location);
            },
            $data->location
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
        return isset($data->location);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/conf/libraries/{$this->library->code}/locations";
    }
}
