<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;

class User
{
    protected $_user_id;
    protected $client;
    protected $data;

    public function __construct(Client $client = null, $user_id = null, $data = [])
    {
        $this->_user_id = $user_id;
        $this->client = $client;
        $this->data = $data;
        // $this->rows = new Rows($this->path, $this->client);
    }

    public static function fromResponse(Client $client = null, $data)
    {
        return new User($client, $data->primary_id, $data);
    }

    public function hasFullRecord()
    {
        return isset($this->data->user_identifier);
    }

    public function fetch()
    {
        if ($this->hasFullRecord()) {
            return;
        }
        $data = $this->client->getJSON('/users/' . $this->_user_id);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getBarcode()
    {
        foreach ($this->user_identifier as $identifier) {
            if ($identifier->id_type->value == 'BARCODE') {
                return $identifier->value;
            }
        }
        return null;
    }

    public function getUniversityId()
    {
        foreach ($this->user_identifier as $identifier) {
            if ($identifier->id_type->value == 'UNIV_ID') {
                return $identifier->value;
            }
        }
        return null;
    }

    public function getIds()
    {
        $ids = [$this->primary_id];
        foreach ($this->user_identifier as $identifier) {
            $ids[] = $identifier->value;
        }
        return $ids;
    }

    public function __get($key)
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        } else {
            // If initialized from a search, we don't have the full record.
            // Let's fetch it.
            $this->fetch();
            if (isset($this->data->{$key})) {
                return $this->data->{$key};
            }
        }
    }
}
