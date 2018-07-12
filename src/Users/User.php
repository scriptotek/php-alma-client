<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;

class User
{
    /**
     * This class is a ghost object that lazy loads the full record only when needed.
     * If $initialized is false, it means we haven't yet loaded the full record.
     * We can still have incomplete data from a search response.
     */
    protected $initialized = false;

    protected $client;

    protected $id;

    protected $_identifiers;

    /* @var \stdClass */
    protected $data;

    /**
     * Create a user from a search response containing an incomplete user record.
     *
     * @param Client    $client
     * @param \stdClass $data
     *
     * @return User
     */
    public static function fromSearchResponse(Client $client, \stdClass $data)
    {
        return (new self($client, $data->primary_id))
            ->init($data);
    }

    /**
     * User constructor.
     *
     * @param Client $client
     * @param string $id
     */
    public function __construct(Client $client, $id)
    {
        $this->client = $client;
        $this->id = $id;
    }

    /**
     * Load data on this User object. Chainable method.
     *
     * @param \stdClass $data
     *
     * @return User
     */
    public function init($data = null)
    {
        if ($this->initialized) {
            return $this;
        }

        if (is_null($data)) {
            $data = $this->client->getJSON('/users/' . $this->id);
        }

        if (isset($data->user_identifier)) {
            $this->_identifiers = new UserIdentifiers($data->primary_id, $data->user_identifier);
            $this->initialized = true;
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Get the complete user record.
     *
     * @return \stdClass
     */
    public function getData()
    {
        return $this->init()->data;
    }

    /**
     * Get the primary id. No need to load the full record for this.
     *
     * @return string|null
     */
    public function getPrimaryId()
    {
        return $this->primary_id;
    }

    /**
     * Get the user identifiers.
     *
     * @return UserIdentifier[]
     */
    public function getIdentifiers()
    {
        return $this->init()->_identifiers;
    }

    /**
     * Magic!
     */
    public function __get($key)
    {
        // If there's a getter method, call it.
        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        // If the property is defined in our data object, return it.
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }

        // Load the full record if needed.
        $this->init();

        // If there's a getter method on the UserIdentifiers object
        // (getBarcode, getPrimaryId), call it.
        if (method_exists($this->identifiers, $method)) {
            return $this->identifiers->$method();
        }

        // Re-check if there's a property on our data object
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }
    }
}
