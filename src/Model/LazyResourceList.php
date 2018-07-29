<?php

namespace Scriptotek\Alma\Model;

/**
 * The LazyResourceList extends the LazyResource class with functionality for
 * working with lists of resources, such as holdings, items, loans, etc.
 */
abstract class LazyResourceList extends LazyResource implements \Countable
{
    /* @var array */
    protected $resources = [];

    /**
     * Get all the resources.
     *
     * @return array
     */
    public function all()
    {
        return $this->init()->resources;
    }

    /**
     * Number of resources.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The number of resources as an integer.
     */
    public function count()
    {
        return count($this->all());
    }
}
