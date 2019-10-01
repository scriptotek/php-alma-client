<?php

namespace Scriptotek\Alma\Analytics;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Exception\RequestFailed;
use Scriptotek\Alma\Model\LazyResource;
use Scriptotek\Alma\Model\PaginatedList;

/**
 * A single Report resource.
 */
class Report extends LazyResource implements \Iterator, \Countable
{
    use PaginatedList;

    /** @var Client */
    protected $client;

    /** @var string */
    public $path;

    /** @var string */
    public $filter;

    /** @var array */
    protected $headers = [];

    /** @var string */
    protected $resumptionToken = null;

    /** @var bool */
    protected $isFinished = false;

    /** @var int */
    public $chunkSize = 1000;

    /** @var Row[] */
    protected $resources = [];

    public static $maxAttempts = 5;

    public static $retryDelayTime = 3;

    public function __construct(Client $client = null, $path = null, $headers = [], $filter = null)
    {
        parent::__construct($client);

        $this->path = $path;
        $this->headers = $headers;
        $this->filter = $filter;
    }

    /**
     * @deprecated
     *
     * @return $this
     */
    public function getRows()
    {
        return $this;
    }

    public function getHeaders()
    {
        if (!count($this->headers)) {
            $this->fetchBatch();
        }

        return $this->headers;
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return '/analytics/reports';
    }

    /**
     * Convert a retrieved resource to an object.
     *
     * @param $data
     *
     * @return mixed
     */
    protected function convertToResource($data)
    {
        return new Row($data, $this->headers);
    }

    /**
     * Note: chunkSize must be between 25 and 1000.
     *
     * @param int $attempt
     * @param int $chunkSize
     *
     * @return void
     */
    protected function fetchBatch($attempt = 1, $chunkSize = null)
    {
        if ($this->isFinished) {
            return;
        }

        $results = $this->client->getXML($this->url('', [
            'path'   => $this->resumptionToken ? null : $this->path,
            'limit'  => $chunkSize ?: $this->chunkSize,
            'token'  => $this->resumptionToken,
            'filter' => $this->filter ? str_replace(['\''], ['&apos;'], $this->filter) : null,
        ]));

        $results->registerXPathNamespaces([
            'rowset'  => 'urn:schemas-microsoft-com:xml-analysis:rowset',
            'xsd'     => 'http://www.w3.org/2001/XMLSchema',
            'saw-sql' => 'urn:saw-sql',
        ]);

        $this->readColumnHeaders($results);

        $rows = $results->all('//Row');

        foreach ($rows as $row) {
            $this->resources[] = $this->convertToResource($row);
        }

        $this->resumptionToken = $results->text('/report/QueryResult/ResumptionToken') ?: $this->resumptionToken;
        $this->isFinished = ($results->text('/report/QueryResult/IsFinished') === 'true');

        if (!count($rows) && !$this->isFinished) {
            // If the Analytics server spends too long time preparing the results, it can
            // sometimes return an empty result set. If this happens, we should just wait
            // a little and retry the request.
            // See: https://bitbucket.org/uwlib/uwlib-alma-analytic-tools/wiki/Understanding_Analytic_GET_Requests#!analytic-still-loading
            if ($attempt >= self::$maxAttempts) {
                // Give up
                throw new RequestFailed(
                    'Not getting any data from the Analytics server - max number of retries exhausted.'
                );
            }
            // Sleep for a few seconds, then retry
            sleep(self::$retryDelayTime);
            $this->fetchBatch($attempt + 1);
        }
    }

    protected function fetchData()
    {
        do {
            $this->fetchBatch();
        } while (!$this->isFinished);
    }

    /**
     * Read column headers from response, and check that we got the right number of columns back.
     *
     * @param QuiteSimpleXMLElement $results
     */
    protected function readColumnHeaders(QuiteSimpleXMLElement $results)
    {
        $headers = array_map(function (QuiteSimpleXMLElement $node) {
            return $node->attr('saw-sql:columnHeading');
        }, $results->all('//xsd:complexType[@name="Row"]/xsd:sequence/xsd:element[position()>1]'));

        if (!count($headers)) {
            // No column headers included in this response. They're only
            // included in the first response, so that's probably fine.
            return;
        }

        if (!count($this->headers)) {
            $this->headers = $headers;

            return;
        }

        if (count($headers) != count($this->headers)) {
            throw new \RuntimeException(sprintf(
                'The number of returned columns (%d) does not match the number of assigned headers (%d).',
                count($headers),
                count($this->headers)
            ));
        }
    }

    /**
     * Check if we have the full representation of our data object. We cannot
     * really know from the data object alone, but when this method is called
     * we should have all the data.
     *
     * @param \stdClass $data
     *
     * @return bool
     */
    protected function isInitialized($data)
    {
        return true;
    }

    /**
     * Total number of resources. Note that we don't get this number from API upfront,
     * so we have to fetch all the rows to find out.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int
     */
    public function count()
    {
        return count($this->init()->resources);
    }

    /**
     * Magic!
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if ($key === 'headers') {
            return $this->getHeaders();
        }
        if ($key === 'rows') {
            return $this;
        }
    }
}
