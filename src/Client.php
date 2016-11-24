<?php

namespace Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Scriptotek\Alma\Analytics\Analytics;
use Scriptotek\Alma\Bibs\Bibs;
use Scriptotek\Alma\Exception\ClientException;
use Scriptotek\Alma\Users\Users;
use Scriptotek\Sru\Client as SruClient;

/**
 * Alma client.
 */
class Client
{
    public $baseUrl;

    /** @var string Alma zone (institution or network) */
    public $zone;

    /** @var string Alma Developers Network API key for this zone */
    public $key;

    /** @var string Network zone instance */
    public $nz;

    /** @var HttpClient */
    protected $httpClient;

    /** @var SruClient */
    public $sru;

    /** @var Bibs */
    public $bibs;

    /** @var Analytics */
    public $analytics;

    /** @var Users */
    public $users;

    /**
     * Create a new client to connect to a given Alma instance.
     *
     * @param string     $key        API key
     * @param string     $region     Hosted region code, used to build base URL
     * @param string     $zone       Alma zone (Either Zones::INSTITUTION or Zones::NETWORK)
     * @param HttpClient $httpClient
     *
     * @throws \ErrorException
     */
    public function __construct($key = null, $region = 'eu', $zone = Zones::INSTITUTION, HttpClient $httpClient = null)
    {
        $this->key = $key;
        $this->setRegion($region);
        $this->httpClient = $httpClient ?: new HttpClient();
        $this->zone = $zone;
        $this->bibs = new Bibs($this);  // Or do some magic instead?
        $this->analytics = new Analytics($this);  // Or do some magic instead?
        $this->users = new Users($this);  // Or do some magic instead?
        if ($zone == Zones::INSTITUTION) {
            $this->nz = new self(null, $region, Zones::NETWORK, $this->httpClient);
        } elseif ($zone != Zones::NETWORK) {
            throw new ClientException('Invalid zone name.');
        }
    }

    /**
     * Attach an SRU client (so you can search for Bib records).
     *
     * @param SruClient $sru
     */
    public function setSruClient(SruClient $sru)
    {
        $this->sru = $sru;
    }

    /**
     * Set the API key for this Alma instance.
     *
     * @param string $key The API key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the Alma region code ('na' for North America, 'eu' for Europe, 'ap' for Asia Pacific).
     *
     * @param $regionCode
     * @return $this
     * @throws \ErrorException
     */
    public function setRegion($regionCode)
    {
        if (!in_array($regionCode, ['na', 'eu', 'ap'])) {
            throw new ClientException('Invalid region code');
        }
        $this->baseUrl = 'https://api-' . $regionCode . '.hosted.exlibrisgroup.com/almaws/v1';

        return $this;
    }

    /**
     * @param $url
     *
     * @return string
     */
    protected function getFullUrl($url)
    {
        return $this->baseUrl . $url;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getHttpOptions($options = [])
    {
        if (!$this->key) {
            throw new ClientException('No API key defined for ' . $this->zone);
        }
        $defaultOptions = [
            'headers' => ['Authorization' => 'apikey ' . $this->key],
        ];

        return array_merge_recursive($defaultOptions, $options);
    }

    /**
     * Make a HTTP request.
     *
     * @param string $method
     * @param string $url
     * @param array  $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request($method, $url, $options = [])
    {
        try {
            return $this->httpClient->request($method, $this->getFullUrl($url), $this->getHttpOptions($options));
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->handleError($e->getResponse());
        }
    }

    public function handleError($response)
    {
        $msg = $response->getBody();
        throw new ClientException('Client error ' . $response->getStatusCode() . ': ' . $msg);
    }

    /**
     * Make a GET request, accepting JSON.
     *
     * @param string $url
     * @param array  $query
     *
     * @return mixed
     */
    public function getJSON($url, $query = [])
    {
        $response = $this->request('GET', $url, [
            'query'   => $query,
            'headers' => ['Accept' => 'application/json'],
        ]);

        return json_decode($response->getBody());
    }

    /**
     * Make a GET request, accepting XML.
     *
     * @param string $url
     * @param array  $query
     *
     * @return mixed
     */
    public function getXML($url, $query = [])
    {
        $response = $this->request('GET', $url, [
            'query'   => $query,
            'headers' => ['Accept' => 'application/xml'],
        ]);

        return new QuiteSimpleXMLElement(strval($response->getBody()));
    }

    /**
     * Make a PUT request.
     *
     * @param string $url
     * @param $data
     * @param string $contentType
     * @return bool
     */
    public function put($url, $data, $contentType = 'application/json')
    {
        $data = json_encode($data);

        $response = $this->request('PUT', $url, [
            'body'    => $data,
            'headers' => [
                'Content-Type' => $contentType,
                'Accept'       => $contentType,
            ],
        ]);

        return $response->getStatusCode() == '200';
        // TODO: Check if there are other success codes that can be returned
    }

    /**
     * Make a PUT request.
     *
     * @param string $url
     * @param $data
     *
     * @return bool
     */
    public function putXML($url, $data)
    {
        return $this->put($url, $data, 'application/xml');
    }

    /**
     * Get the redirect target location of an URL, or null if not a redirect.
     *
     * @param string $url
     * @param array  $query
     *
     * @return string|null
     */
    public function getRedirectLocation($url, $query = [])
    {
        try {
            $response = $this->httpClient->request('GET', $this->getFullUrl($url), $this->getHttpOptions([
                'query'           => $query,
                'headers'         => ['Accept' => 'application/json'],
                'allow_redirects' => false,
            ]));
        } catch (RequestException $e) {
            // We receive a 400 if the barcode is invalid
            // if ($e->hasResponse()) {
            //     echo $e->getResponse()->getStatusCode() . "\n";
            //     echo $e->getResponse()->getBody() . "\n";
            // }
            return;
        }
        $locations = $response->getHeader('Location');

        return count($locations) ? $locations[0] : null;
    }
}
