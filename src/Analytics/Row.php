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
        return isset($this->byIndex[$offset]);
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Sorry, column values cannot be modified.');
    }

    public function offsetGet($offset)
    {
        return isset($this->byIndex[$offset]) ? $this->byIndex[$offset] : null;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->byIndex);
    }

    public function count()
    {
        return count($this->byIndex);
    }
}
