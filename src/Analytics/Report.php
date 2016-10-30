<?php

namespace Scriptotek\Alma\Analytics;

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
        // $this->rows = new Rows($this->path, $this->client);
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
            'path' => $this->path,
            'limit' => $this->chunkSize,
            'token' => $resumptionToken,
            'filter' => $this->filter ? str_replace(['\''], ['&apos;'], $this->filter) : null,
        ]);
        $results->registerXPathNamespaces([
            'rowset' => 'urn:schemas-microsoft-com:xml-analysis:rowset',
            'xsd'    => 'http://www.w3.org/2001/XMLSchema',
        ]);

        return $results;
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

            $headers = array_map(function ($node) {
                return $node->attr('name');
            }, $results->all('//xsd:complexType[@name="Row"]/xsd:sequence/xsd:element[position()>1]'));

            if (count($headers)) {
                if (!count($this->headers)) {
                    $this->headers = $headers;
                } elseif (count($headers) != count($this->headers)) {
                    throw new \RuntimeException('Number of headers doesn\'t match: ' . count($headers) . ' != ' . count($this->headers));
                }
            }

            foreach ($results->all('//rowset:Row') as $row) {
                yield new Row($row, $this->headers);
            }

            $resumptionToken = $results->text('/report/QueryResult/ResumptionToken') ?: $resumptionToken;
            $isFinished = ($results->text('/report/QueryResult/IsFinished') == 'true');
        }
    }
}
