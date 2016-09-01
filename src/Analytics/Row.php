<?php

namespace Scriptotek\Alma\Analytics;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;

class Row implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $byIndex = [];
    protected $byHeader = [];
    protected $headers;

    public function __construct(QuiteSimpleXMLElement $data, $headers)
    {
        $this->headers = $headers;
        foreach ($headers as $idx => $header) {
            $value = $data->text('rowset:Column' . ($idx + 1)) ?: null;
            $this->byIndex[$idx] = $value;
            $this->byHeader[$header] = $value;
        }
    }

    public function __get($name)
    {
        return $this->byHeader[$name];
    }

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Sorry, column values cannot be modified.');
    }

    public function offsetExists($offset)
    {
        return isset($this->byIndex[$offset]) || isset($this->byHeader[$offset]);
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Sorry, column values cannot be modified.');
    }

    public function offsetGet($offset)
    {
        if (isset($this->byIndex[$offset])) {
            return $this->byIndex[$offset];
        }
        if (isset($this->byHeader[$offset])) {
            return $this->byHeader[$offset];
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->byHeader);
    }

    public function count()
    {
        return count($this->byIndex);
    }

    public function toArray()
    {
        return $this->byHeader;
    }
}
