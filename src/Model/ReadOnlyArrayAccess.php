<?php

namespace Scriptotek\Alma\Model;

trait ReadOnlyArrayAccess
{
    public function offsetSet($offset, $value): void
    {
        throw \Exception('Not implemented');
    }

    public function offsetUnset($offset): void
    {
        throw \Exception('Not implemented');
    }

    public function offsetExists($offset): bool
    {
        return $this->get($offset)->exists();
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }
}
