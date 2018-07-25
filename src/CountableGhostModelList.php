<?php

namespace Scriptotek\Alma;

abstract class CountableGhostModelList extends GhostModel implements \Countable
{
    /* @var array */
    protected $resources;

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
     * Number of holdings.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The number of holdings as an integer.
     */
    public function count()
    {
        return count($this->all());
    }
}