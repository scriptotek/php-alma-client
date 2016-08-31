<?php

namespace Scriptotek\Alma\Analytics;

use Scriptotek\Alma\Client;

class Report
{
    public $path;
    public $chunkSize = 1000;

    /** @var Client */
    protected $client;

    protected $headers = [];

    public function __construct($path = null, Client $client = null)
    {
        $this->path = $path;
        $this->client = $client;
        // $this->rows = new Rows($this->path, $this->client);
    }

    public function __get($key)
    {
        if ($key == 'rows') {
            return $this->getRows();
        }
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    protected function fetchRows($resumptionToken = null)
    {
        $results = $this->client->getXML('/analytics/reports', [
            'path' => $this->path,
            'limit' => $this->chunkSize,
            'token' => $resumptionToken,
        ]);
        $results->registerXPathNamespaces([
            'rowset' => 'urn:schemas-microsoft-com:xml-analysis:rowset',
            'xsd'    => 'http://www.w3.org/2001/XMLSchema',
        ]);

        return $results;
    }

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
                print "yield!\n";
                // var_dump($this->headers);

                yield new Row($row, $this->headers);
            }

            $resumptionToken = $results->text('/report/QueryResult/ResumptionToken') ?: $resumptionToken;
            $isFinished = ($results->text('/report/QueryResult/IsFinished') == 'true');
        }
    }
}
