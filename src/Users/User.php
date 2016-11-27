<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;

class User
{
    protected $_user_id;
    protected $client;

    /* @var \stdClass */
    protected $data;

    /**
     * User constructor.
     *
     * @param Client|null $client
     * @param string $user_id
     * @param \stdClass $data
     */
    public function __construct(Client $client = null, $user_id = null, \stdClass $data = null)
    {
        $this->_user_id = $user_id;
        $this->client = $client;
        $this->data = $data;
    }

    /**
     * Create a user from an API response.
     *
     * @param Client $client
     * @param \stdClass $data
     * @return User
     */
    public static function fromResponse(Client $client, \stdClass $data)
    {
        return new User($client, $data->primary_id, $data);
    }

    /**
     * Do we have the full record, or only the fields from the search response?
     *
     * @return bool
     */
    public function hasFullRecord()
    {
        return isset($this->data->user_identifier);
    }

    /**
     * Fetch the full record.
     */
    public function fetch()
    {
        if ($this->hasFullRecord()) {
            return;
        }
        $this->data = $this->client->getJSON('/users/' . $this->_user_id);
    }

    /**
     * @return \stdClass
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get user identifier of a given type, like 'BARCODE' or 'UNIV_ID'.
     *
     * @param string $id_type
     * @return string|null
     */
    public function getIdOfType($id_type)
    {
        foreach ($this->user_identifier as $identifier) {
            if ($identifier->id_type->value == $id_type) {
                return $identifier->value;
            }
        }
        return null;
    }

    /**
     * Get the barcode.
     *
     * @return null|string
     */
    public function getBarcode()
    {
        return $this->getIdOfType('BARCODE');
    }

    /**
     * Get the university id.
     *
     * @return null|string
     */
    public function getUniversityId()
    {
        return $this->getIdOfType('UNIV_ID');
    }

    /**
     * Get a flat array of all the user IDs.
     *
     * @return string[]
     */
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
