<?php

namespace Scriptotek\Alma\Facades;

use Illuminate\Support\Facades\Facade;

class Alma extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'alma';
    }
}
