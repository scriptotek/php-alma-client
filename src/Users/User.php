<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single User resource.
 */
class User extends LazyResource
{
    /**
     * The primary id or some other id that can be used to fetch user information.
     *
     * @var string
     */
    public $id;

    /**
     * @var UserIdentifiers
     */
    protected $_identifiers;

    /**
     * @var ContactInfo
     */
    protected $_contact_info;

    /**
     * @var Loans
     */
    public $loans;

    /**
     * @var Fees
     */
    public $fees;

    /**
     * @var Requests
     */
    public $requests;

    /**
     * User constructor.
     *
     * @param Client $client
     * @param string $id
     */
    public function __construct(Client $client, $id)
    {
        parent::__construct($client);
        $this->id = $id;
        $this->loans = Loans::make($this->client, $this);
        $this->fees = Fees::make($this->client, $this);
        $this->requests = Requests::make($this->client, $this->url('/requests'));
    }

    /**
     * Get the user id the object was constructed with. This might or might not be the primary id.
     * The only usefulness of this method over getPrimaryId() is that it will not trigger loading of the full object.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->id;
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
     * @return UserIdentifiers
     */
    public function getIdentifiers()
    {
        return $this->init()->_identifiers;
    }

    /**
     * Get the user's contact info.
     */
    public function getContactInfo()
    {
        return $this->init()->_contact_info;
    }

    /**
     * Save the user
     * 
     * @return string The API response body
     */
    public function save()
    {
        $this->init();
        return $this->client->put($this->url(), json_encode($this->jsonSerialize()));
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     *
     * @return bool
     */
    protected function isInitialized($data)
    {
        return isset($data->user_identifier);
    }

    /**
     * Called when data is available to be processed.
     *
     * @param mixed $data
     */
    protected function onData($data)
    {
        $this->_identifiers = UserIdentifiers::make($this->client, $data);
        $this->_contact_info = ContactInfo::make($this->client, $data->contact_info);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return sprintf('/users/%s', rawurlencode($this->id));
    }

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
