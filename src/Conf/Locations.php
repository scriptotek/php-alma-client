<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

/**
 * Iterable collection of Location resources.
 */
class Locations extends LazyResourceList implements \ArrayAccess, \Countable, \Iterator
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /**
     * The Library this Locations list belongs to.
     *
     * @var Library
     */
    protected $library;

    /**
     * Locations constructor.
     *
     * @param Client  $client
     * @param Library $library
     */
    public function __construct(Client $client, Library $library)
    {
        parent::__construct($client, 'location');
        $this->library = $library;
    }

    /**
     * Get a single location by its location code.
     *
     * @param string $code
     *
     * @return Location
     */
    public function get($code)
    {
        return Location::make($this->client, $this->library, $code);
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     *
     * @return Location
     */
    protected function convertToResource($data)
    {
        return Location::make($this->client, $this->library, $data->code)
            ->init($data);
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
