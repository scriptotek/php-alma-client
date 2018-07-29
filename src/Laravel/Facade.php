<?php

namespace Scriptotek\Alma\Laravel;

use Scriptotek\Alma\Client as AlmaClient;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return AlmaClient::class;
    }
}
