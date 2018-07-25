<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\ArrayAccessResource;
use Scriptotek\Alma\CountableGhostModelList;
use Scriptotek\Alma\IterableResource;

class Libraries extends CountableGhostModelList implements \ArrayAccess, \Countable, \Iterator
{
    use ArrayAccessResource;
    use IterableResource;

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

    protected function setData(\stdClass $data)
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
