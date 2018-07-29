<?php

namespace Scriptotek\Alma\Conf;

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
     * Get resource.
     *
     * @param string $code
     * @return Library
     */
    public function get($code)
    {
        return Library::make($this->client, $code);
    }

    protected function setData($data)
    {
        $this->resources = array_map(
            function (\stdClass $library) {
                return Library::make($this->client, $library->code)
                    ->init($library);
            },
            $data->library
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
        return isset($data->library);
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
