<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;

class User
{
    public $primary_id;
    protected $client;
    protected $data;

    public function __construct(Client $client = null, $primary_id = null, $data = [])
    {
        $this->primary_id = $primary_id;
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
        $data = $this->client->getJSON('/users/' . $this->primary_id);
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

    public function __get($key)
    {
        return isset($this->data->{$key}) ? $this->data->{$key} : null;
    }
}
