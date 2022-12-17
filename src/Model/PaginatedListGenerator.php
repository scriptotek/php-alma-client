<?php

namespace Scriptotek\Alma\Model;

trait PaginatedListGenerator
{
    /* @var integer */
    protected $position = 0;

    /**
     * Rewind the Iterator to the first element.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        if ($this->position > 0) {
            throw new \Exception('Cannot rewind a generator that was already run');
        }
    }

    /**
     * Checks if current position is valid.
     *
     * @link http://php.net/manual/en/iterator.valid.php
     */
    public function valid(): bool
    {
        if (!isset($this->resources[0])) {
            $this->fetchBatch();
        }

        return isset($this->resources[0]);
    }

    /**
     * Return the current element.
     *
     * @link http://php.net/manual/en/iterator.current.php
     */
    public function current(): mixed
    {
        return array_shift($this->resources);
    }

    /**
     * Move forward to next element.
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Return the key of the current element.
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return int|null Scalar on success, or null on failure.
     */
    public function key(): mixed
    {
        return $this->position;
    }
}
