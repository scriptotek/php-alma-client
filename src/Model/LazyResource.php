<?php

namespace Scriptotek\Alma\Model;

use Psr\Http\Message\UriInterface;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Exception\ResourceNotFound;

/**
 * A LazyResource is anything that has its own URL and therefore can be lazy-loaded.
 * This class implements the basic ghost model functionality.
 *
 * The $initialized property indicates if the model is loaded or not.
 */
abstract class LazyResource extends Model
{
    /**
     * This class is a ghost object that lazy loads the full record only when needed.
     * If $initialized is false, it means we haven't yet loaded the full record.
     * We can still have incomplete data from a search response.
     */
    protected $initialized = false;

    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     * @return boolean
     */
    abstract protected function isInitialized($data);

    /**
     * Load data onto this object. Chainable method.
     *
     * @param \stdClass $data
     *
     * @return self
     */
    public function init($data = null)
    {
        if ($this->initialized) {
            return $this;
        }

        if (is_null($data)) {
            $data = $this->fetchData();
        }

        if (!is_null($data) && $this->isInitialized($data)) {
            $this->initialized = true;
        }

        $this->data = $data;
        if ($this->initialized) {
            $this->setData($data);
        }

        return $this;
    }

    /**
     * Get the model data.
     */
    protected function fetchData()
    {
        return $this->client->getJSON($this->url());
    }

    /**
     * Store data onto object. Can be overriden.
     *
     * @param mixed $data
     */
    protected function setData($data)
    {
    }

    /**
     * Get the raw data object.
     */
    public function getData()
    {
        return $this->init()->data;
    }

    /**
     * Check if the object exists.
     */
    public function exists()
    {
        try {
            $this->init();
        } catch (ResourceNotFound $ex) {
        }

        return $this->initialized;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    abstract protected function urlBase();

    /**
     * Build a full URL for a resource.
     *
     * @param string $url
     * @param array $query
     * @return UriInterface
     */
    protected function url($url = '', $query = [])
    {
        return $this->client->buildUrl($this->urlBase() . $url, $query);
    }
}
