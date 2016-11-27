<?php

namespace Scriptotek\Alma\Bibs;

class Item
{
    protected $_data;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function __get($key)
    {
        if (isset($this->_data->item_data->{$key})) {
            return $this->_data->item_data->{$key};
        }
        if (isset($this->_data->holding_data->{$key})) {
            return $this->_data->holding_data->{$key};
        }
        if (isset($this->_data->bib_data->{$key})) {
            return $this->_data->bib_data->{$key};
        }
    }
}
