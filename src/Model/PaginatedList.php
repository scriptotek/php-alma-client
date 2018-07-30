<?php

namespace Scriptotek\Alma\Model;

trait PaginatedList
{
    /* @var integer */
    protected $position = 0;

    /**
     * Fetch all the data.
     */
    protected function fetchData()
    {
        return iterator_to_array($this);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Checks if current position is valid.
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean
     */
    public function valid()
    {
        if (!isset($this->resources[$this->position])) {
            $this->fetchBatch();
        }

        return isset($this->resources[$this->position]);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed
     */
    public function current()
    {
        return $this->resources[$this->position];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return integer|null Scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }
}
