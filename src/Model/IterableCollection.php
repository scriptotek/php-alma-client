<?php

namespace Scriptotek\Alma\Model;

trait IterableCollection
{
    protected $position = 0;

    public function current(): mixed
    {
        return $this->init()->resources[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < $this->count();
    }
}
