<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\IterableCollection;

/**
 * Iterable collection of Library resources.
 */
class Libraries extends LazyResourceList implements \ArrayAccess, \Countable, \Iterator
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    /**
     * Locations constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, 'library');
    }

    /**
     * Get a single library by its library code.
     *
     * @param string $code
     * @return Library
     */
    public function get($code)
    {
        return Library::make($this->client, $code);
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     * @return Library
     */
    protected function convertToResource($data)
    {
        return Library::make($this->client, $data->code)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/conf/libraries";
    }
}
