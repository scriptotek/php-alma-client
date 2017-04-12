<?php

namespace Scriptotek\Alma\Analytics;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Scriptotek\Alma\Client;

/**
 * @property \Generator|Row[] rows
 */
class Report
{
    public $path;
    public $chunkSize = 1000;

    /** @var Client */
    protected $client;

    protected $headers = [];

    public function __construct(Client $client = null, $path = null, $headers = [], $filter = null)
    {
        $this->path = $path;
        $this->client = $client;

        $this->headers = $headers;
        $this->filter = $filter;
    }

    public function __get($key)
    {
        if ($key == 'rows') {
            return $this->getRows();
        }
        if ($key == 'headers') {
            return $this->headers;
        }
    }

    protected function fetchRows($resumptionToken = null)
    {
        $results = $this->client->getXML('/analytics/reports', [
            'path'   => $resumptionToken ? null : $this->path,
            'limit'  => $this->chunkSize,
            'token'  => $resumptionToken,
            'filter' => $this->filter ? str_replace(['\''], ['&apos;'], $this->filter) : null,
        ]);
        $results->registerXPathNamespaces([
            'rowset' => 'urn:schemas-microsoft-com:xml-analysis:rowset',
            'xsd'    => 'http://www.w3.org/2001/XMLSchema',
        ]);

        $this->readColumnHeaders($results);

        return $results;
    }

    /**
     * Read column headers from response, and check that we got the right number of columns back.
     *
     * @param QuiteSimpleXMLElement $results
     */
    protected function readColumnHeaders(QuiteSimpleXMLElement $results)
    {
        $headers = array_map(function (QuiteSimpleXMLElement $node) {
            return $node->attr('name');
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
            throw new \RuntimeException(sprintf('The number of returned columns (%d) does not match the number of assigned headers (%d).',
                count($headers), count($this->headers)));
        }
    }

    /**
     * @return \Generator|Row[]
     */
    public function getRows()
    {
        $isFinished = false;
        $resumptionToken = null;

        while (!$isFinished) {
            $results = $this->fetchRows($resumptionToken);

            foreach ($results->all('//rowset:Row') as $row) {
                yield new Row($row, $this->headers);
            }

            $resumptionToken = $results->text('/report/QueryResult/ResumptionToken') ?: $resumptionToken;
            $isFinished = ($results->text('/report/QueryResult/IsFinished') == 'true');
        }
    }
}
