<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\IterableCollection;
use Scriptotek\Alma\Model\LazyResourceList;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

/**
 * Iterable collection of Request resources.
 */
class Requests extends LazyResourceList implements \Countable, \Iterator, \ArrayAccess
{
    use ReadOnlyArrayAccess;
    use IterableCollection;

    protected $_urlBase;

    /**
     * Requests constructor.
     *
     * @param Client $client
     * @param string $url
     */
    public function __construct(Client $client, $url)
    {
        parent::__construct($client, 'user_request');
        $this->_urlBase = $url;
    }

    /**
     * Convert a data element to a resource object.
     *
     * @param $data
     * @return Request
     */
    protected function convertToResource($data)
    {
        return Request::make($this->client, User::make($this->client, $data->user_primary_id), $data->request_id)
            ->init($data);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return $this->_urlBase;
    }
}
