<?php

namespace Scriptotek\Alma;

trait IterableResource
{
    /*********************************************************
     * Iterator
     *********************************************************/

    protected $position = 0;
    protected $_resources;

    public function current()
    {
        return $this->init()->resources[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return $this->position < $this->count();
    }
}
