<?php

namespace Scriptotek\Alma;

use ReflectionClass;

class ResourceList
{
    public $client;
    public $factory;
    protected $resourceName;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    protected function factory($args)
    {
        array_unshift($args, $this->client);

        $reflect = new ReflectionClass($this->resourceName);
        $instance = $reflect->newInstanceArgs($args);
        if (method_exists($instance, 'fetch')) {
            $instance->fetch();
        }

        return $instance;
    }

    /**
     * Returns a single resource.
     *
     * @return ResourceInterface
     */
    public function get()
    {
        $args = func_get_args();

        return $this->factory($args);
    }
}
