<?php

namespace Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\NetworkException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
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
    protected $http;

    /** @var MessageFactory */
    protected $messageFactory;

    /** @var UriFactory */
    protected $uriFactory;

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

    /** @var float Number of seconds to sleep before retrying */
    public $sleepTimeOnRetry = 0.5;

    /**
     * Create a new client to connect to a given Alma instance.
     *
     * @param string         $key            API key
     * @param string         $region         Hosted region code, used to build base URL
     * @param string         $zone           Alma zone (Either Zones::INSTITUTION or Zones::NETWORK)
     * @param HttpClient     $http
     * @param MessageFactory $messageFactory
     * @param UriFactory     $uriFactory
     *
     * @throws \ErrorException
     */
    public function __construct(
        $key = null,
        $region = 'eu',
        $zone = Zones::INSTITUTION,
        HttpClient $http = null,
        MessageFactory $messageFactory = null,
        UriFactory $uriFactory = null
    ) {
        $this->http = new PluginClient(
            $http ?: HttpClientDiscovery::find(), [
                new ContentLengthPlugin(),
                new ErrorPlugin(),
            ]
        );
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
        $this->uriFactory = $uriFactory ?: UriFactoryDiscovery::find();

        $this->key = $key;
        $this->setRegion($region);

        $this->zone = $zone;
        $this->bibs = new Bibs($this);  // Or do some magic instead?
        $this->analytics = new Analytics($this);  // Or do some magic instead?
        $this->users = new Users($this);  // Or do some magic instead?
        if ($zone == Zones::INSTITUTION) {
            $this->nz = new self(null, $region, Zones::NETWORK, $this->http, $this->messageFactory, $this->uriFactory);
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
     *
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
     *
     * @throws \ErrorException
     *
     * @return $this
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
     * @param string $url
     * @param array  $query
     *
     * @return UriInterface
     */
    protected function getFullUri($url, $query = [])
    {
        $query['apikey'] = $this->key;

        return $this->uriFactory->createUri($this->baseUrl . $url)
            ->withQuery(http_build_query($query));
    }

    /**
     * Make a synchronous HTTP request and return a PSR7 response if successful.
     * In the case of intermittent errors (connection problem, 429 or 5XX error), the request is
     * attempted a maximum of {$this->maxAttempts} times with a sleep of {$this->sleepTimeOnRetry}
     * between each attempt to avoid hammering the server.
     *
     * @param RequestInterface $request
     * @param int              $attempt
     *
     * @return ResponseInterface
     */
    public function request(RequestInterface $request, $attempt = 1)
    {
        if (!$this->key) {
            throw new ClientException('No API key defined for ' . $this->zone);
        }

        try {
            return $this->http->sendRequest($request);
        } catch (HttpException $e) {
            // Thrown for 400 level errors

            if ($e->getResponse()->getStatusCode() == '429') {
                // We've run into the "Max 25 API calls per institution per second" limit.
                // Wait a sec and retry, unless we've tried too many times already.
                if ($attempt > $this->maxAttempts) {
                    throw new MaxNumberOfAttemptsExhausted($e);
                }
                time_nanosleep(0, $this->sleepTimeOnRetry * 1000000000);

                return $this->request($request, $attempt + 1);
            }

            throw $e;
        } catch (NetworkException $e) {
            // Thrown in case of a networking error

            if ($attempt > $this->maxAttempts) {
                throw new MaxNumberOfAttemptsExhausted($e);
            }
            time_nanosleep(0, $this->sleepTimeOnRetry * 1000000000);

            return $this->request($request, $attempt + 1);
        }
    }

    /**
     * Make a GET request.
     *
     * @param string $url
     * @param array  $query
     * @param string $contentType
     *
     * @return string Response body
     */
    public function get($url, $query = [], $contentType = 'application/json')
    {
        $uri = $this->getFullUri($url, $query);
        $headers = [
            'Accept' => $contentType,
        ];
        $request = $this->messageFactory->createRequest('GET', $uri, $headers);
        $response = $this->request($request);

        return strval($response->getBody());
    }

    /**
     * Make a GET request, accepting JSON.
     *
     * @param string $url
     * @param array  $query
     *
     * @return \stdClass JSON response as an object.
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
     *
     * @return bool
     */
    public function put($url, $data, $contentType = 'application/json')
    {
        $uri = $this->getFullUri($url);
        $headers = [];
        if (!is_null($contentType)) {
            $headers['Content-Type'] = $contentType;
            $headers['Accept'] = $contentType;
        }
        $request = $this->messageFactory->createRequest('PUT', $uri, $headers, $data);
        $response = $this->request($request);

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
        $uri = $this->getFullUri($url, $query);
        $request = $this->messageFactory->createRequest('GET', $uri);

        try {
            $response = $this->request($request);
        } catch (ClientErrorException $e) {
            return;
        }

        $locations = $response->getHeader('Location');

        return count($locations) ? $locations[0] : null;
    }
}
