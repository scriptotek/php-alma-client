<?php

namespace Scriptotek\Alma;

use ReflectionClass;

class Factory
{
    public function make()
    {
        $args = func_get_args();
        $model = 'Scriptotek\\Alma\\Models\\' . array_shift($args);
        $client = array_pop($args);
        $args = $args[0];
        $args[] = $client;
        $reflect = new ReflectionClass($model);
        $instance = $reflect->newInstanceArgs($args);
        if (method_exists($instance, 'fetch')) {
            $instance->fetch();
        }

        return $instance;
    }
}
