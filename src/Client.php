<?php

namespace Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Scriptotek\Alma\Analytics\Analytics;
use Scriptotek\Alma\Bibs\Bibs;
use Scriptotek\Alma\Exception\ClientException;
use Scriptotek\Alma\Exception\MaxNumberOfAttemptsExhausted;
use Scriptotek\Alma\Exception\SruClientNotSetException;
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

    /** @var Client Network zone instance */
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

    /** @var int Max number of retries if we get 429 errors */
    public $maxAttempts = 10;

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
     * Assert that an SRU client is connected. Throws SruClientNotSetException if not.
     *
     * @throws SruClientNotSetException
     */
    public function assertHasSruClient()
    {
        if (!isset($this->sru)) {
            throw new SruClientNotSetException();
        }
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
     * @param array $options
     * @param int $attempt
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request($method, $url, $options = [], $attempt = 1)
    {
        try {
            return $this->httpClient->request($method, $this->getFullUrl($url), $this->getHttpOptions($options));

        } catch (GuzzleClientException $e) {
            // Thrown for 400 level errors

            if ($e->getResponse()->getStatusCode() == '429') {
                // We've run into the "Max 25 API calls per institution per second" limit.
                // Wait a sec and retry, unless we've tried too many times already.
                if ($attempt > $this->maxAttempts) {
                    throw new MaxNumberOfAttemptsExhausted($e);
                }
                time_nanosleep(0, 500000000); // 0.5 s
                return $this->request($method, $url, $options, $attempt + 1);
            }

            $msg = $e->getResponse()->getBody();
            throw new ClientException('Client error ' . $e->getResponse()->getStatusCode() . ': ' . $msg);

        } catch (ConnectException $e) {
            // Thrown in case of a networking error

            if ($attempt > $this->maxAttempts) {
                throw new MaxNumberOfAttemptsExhausted($e);
            }
            time_nanosleep(0, 500000000); // 0.5 s
            return $this->request($method, $url, $options, $attempt + 1);
        }
    }

    /**
     * Make a GET request.
     *
     * @param string $url
     * @param array  $query
     * @param string $contentType
     * @return string  Response body
     */
    public function get($url, $query = [], $contentType = 'application/json')
    {
        $response = $this->request('GET', $url, [
            'query'   => $query,
            'headers' => ['Accept' => $contentType],
        ]);

        return strval($response->getBody());
    }

    /**
     * Make a GET request, accepting JSON.
     *
     * @param string $url
     * @param array  $query
     *
     * @return \stdClass  JSON response as an object.
     */
    public function getJSON($url, $query = [])
    {
        $responseBody = $this->get($url, $query, 'application/json');

        return json_decode($responseBody);
    }

    /**
     * Make a GET request, accepting XML.
     *
     * @param string $url
     * @param array  $query
     *
     * @return QuiteSimpleXMLElement
     */
    public function getXML($url, $query = [])
    {
        $responseBody = $this->get($url, $query, 'application/xml');

        return new QuiteSimpleXMLElement($responseBody);
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
        $response = $this->request('PUT', $url, [
            'body'    => $data,
            'headers' => [
                'Content-Type' => $contentType,
                'Accept'       => $contentType,
            ],
        ]);

        // Consider it a success if status code is 2XX
        return substr($response->getStatusCode(), 0, 1) == '2';
    }

    /**
     * Make a PUT request, sending JSON data.
     *
     * @param string $url
     * @param $data
     *
     * @return bool
     */
    public function putJSON($url, $data)
    {
        $data = json_encode($data);

        return $this->put($url, $data, 'application/json');
    }

    /**
     * Make a PUT request, sending XML data.
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
            // We receive a 400 response if the barcode is invalid.
            return null;
        }
        $locations = $response->getHeader('Location');

        return count($locations) ? $locations[0] : null;
    }
}
