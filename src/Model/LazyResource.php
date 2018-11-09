<?php

namespace Scriptotek\Alma\Model;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
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

    /**
     * @var array Query string parameters
     */
    protected $params = [];

    /**
     * Set request query string parameters.
     *
     * @param $params array
     *
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get the request query string parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     *
     * @return bool
     */
    abstract protected function isInitialized($data);

    /**
     * Load data onto this object. Chainable method.
     *
     * @param \stdClass|QuiteSimpleXMLElement $data
     *
     * @return $this
     */
    public function init($data = null)
    {
        if ($this->initialized) {
            return $this;
        }

        if (is_null($data)) {
            $data = $this->fetchData();
        }

        if ($this->isInitialized($data)) {
            $this->initialized = true;
        }

        $this->data = $data;
        if ($this->initialized) {
            $this->onData($data);
        }

        return $this;
    }

    /**
     * Get and return the model data.
     *
     * @return object
     */
    protected function fetchData()
    {
        return $this->client->getJSON($this->url());
    }

    /**
     * Called when data is available on the object.
     * The resource classes can use this method to process the data.
     *
     * @param mixed $data
     */
    protected function onData($data)
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
     * Build a relative URL for a resource.
     *
     * @param string $path
     * @param array  $query
     *
     * @return string
     */
    protected function url($path = '', $query = [])
    {
        $path = $this->urlBase() . $path;
        $query = http_build_query(array_merge($this->params, $query));

        $url = $path;
        if (!empty($query)) {
            $url .= '?' . $query;
        }

        return $url;
    }
}
