<?php

namespace Scriptotek\Alma;

use Psr\Http\Message\UriInterface;

abstract class GhostModel extends Model
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
            $data = $this->client->getJSON($this->url());
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
     * Store data onto object. Can be overriden.
     *
     * @param \stdClass $data
     */
    protected function setData(\stdClass $data)
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