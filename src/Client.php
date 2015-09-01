<?php

namespace Scriptotek\Alma;

use GuzzleHttp\Client as HttpClient;

/**
 * Alma client
 */
class Client
{
    public $baseUrl;

    /** @var string Alma Developers Network API key */
    public $apiKey;

    /** @var HttpClient */
    protected $httpClient;

    /**
     * Create a new client
     *
     * @param string $apiKey Api key
     * @param string $region
     * @param HttpClient $httpClient
     * @throws \ErrorException
     */
    public function __construct($apiKey = null, $region = 'eu', HttpClient $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->setRegion($region);
        $this->httpClient = $httpClient ?: new HttpClient();
        $this->bibs = new Bibs($this);  // Or do some magic instead?
    }

    /**
     * @param $regionCode
     * @throws \ErrorException
     */
    public function setRegion($regionCode)
    {
        if (!in_array($regionCode, ['na', 'eu', 'ap'])) {
            throw new \ErrorException('Invalid region code');
        }
        $this->baseUrl = 'https://api-' . $regionCode . '.hosted.exlibrisgroup.com/almaws/v1';
    }

    /**
     * @param $url
     * @return string
     */
    protected function getFullUrl($url)
    {
        return $this->baseUrl . $url;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getHttpOptions($options = [])
    {
        $defaultOptions = [
            'headers' => ['Authorization' => 'apikey ' . $this->apiKey]
        ];

        return array_merge_recursive($defaultOptions, $options);
    }

    /**
     * Make a HTTP request.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request($method, $url, $options = [])
    {
        return $this->httpClient->request($method, $this->getFullUrl($url), $this->getHttpOptions($options));
    }

    /**
     * Make a GET request.
     *
     * @param string $url
     * @param array $query
     * @return mixed
     */
    public function get($url, $query = [])
    {
        $response = $this->request('GET', $url, [
            'query' => $query,
            'headers' => ['Accept' => 'application/xml'],
        ]);
        return simplexml_load_string($response->getBody());
    }

    /**
     * Make a GET request.
     *
     * @param string $url
     * @param array $query
     * @return mixed
     */
    public function getJSON($url, $query = [])
    {
        $response = $this->request('GET', $url, [
            'query' => $query,
            'headers' => ['Accept' => 'application/json']
        ]);
        return json_decode($response->getBody());
    }

    /**
     * Make a PUT request.
     *
     * @param string $url
     * @param $data
     * @return bool
     */
    public function put($url, $data)
    {
        $response = $this->request('PUT', $url, [
            'data' => $data,
            'headers' => [
                'Content-Type' => 'application/xml',
                'Accept' => 'application/xml'
            ],
        ]);
        return $response->getStatusCode() == '200';  // TODO: Check if there are other success codes that can be returned
    }
}
