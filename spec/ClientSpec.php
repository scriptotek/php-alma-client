<?php

namespace spec\Scriptotek\Alma;

use GuzzleHttp\Psr7\Response;
use Http\Mock\Client as MockHttp;
use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Zones;
use function GuzzleHttp\Psr7\stream_for;

function str_random()
{
    return strval(rand(10000, 99999));
}

class ClientSpec extends ObjectBehavior
{
    public function let()
    {
        $http = new MockHttp();
        $apiKey = 'DummyApiKey';
        $region = 'eu';
        $this->beConstructedWith($apiKey, $region, Zones::INSTITUTION, $http);

        return $http;
    }

    protected function httpWithResponseBody($body)
    {
        $http = $this->let();
        $response = new Response();
        $response = $response->withBody(stream_for($body));
        $http->addResponse($response);

        return $http;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\Client');
    }

    public function it_only_accepts_valid_regions()
    {
        $this->shouldThrow('Scriptotek\Alma\Exception\ClientException')
            ->duringSetRegion('ux');
    }

    public function it_can_make_GET_requests()
    {
        $responseBody = str_random();
        $this->httpWithResponseBody($responseBody);
        $this->get(str_random())->shouldBe($responseBody);
    }

    public function it_sends_an_API_key_with_each_request()
    {
        $http = $this->httpWithResponseBody(str_random());
        $this->getJSON(str_random());

        // Query string should include apikey
        expect($http->getRequests()[0])->getUri()->getQuery()->toContain('apikey=DummyApiKey');
    }

    public function it_can_request_and_parse_JSON()
    {
        $responseBody = json_encode(['some_key' => 'some_value']);
        $http = $this->httpWithResponseBody($responseBody);

        $this->getJSON(str_random())->some_key->shouldBe('some_value');

        $request = $http->getRequests()[0];
        expect($request->getHeader('Accept')[0])->toBe('application/json');
    }

    public function it_can_request_and_parse_XML()
    {
        $responseBody = "<?xml version=\"1.0\"?>\n<some_key>some_value</some_key>\n";
        $http = $this->httpWithResponseBody($responseBody);

        $xml = $this->getXML(str_random());

        // Request headers should include Accept: application/xml
        expect($http->getRequests()[0])->getHeader('Accept')[0]->toBe('application/xml');

        // Response should be of type QuiteSimpleXMLElement
        $xml->shouldHaveType('Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement');

        // and have the expected value
        $xml->asXML()->shouldBe($responseBody);
    }

    public function it_can_make_PUT_requests()
    {
        $responseBody = str_random();
        $http = $this->httpWithResponseBody($responseBody);

        $this->put(str_random(), str_random(), 'application/json')->shouldBe(true);

        expect($http->getRequests())->toHaveCount(1);
        expect($http->getRequests()[0])->getMethod()->toBe('PUT');
    }

    public function it_can_get_redirect_locations()
    {
        $http = $this->let();
        $response = new Response();
        $response = $response->withHeader('Location', 'http://test.test');
        $http->addResponse($response);

        $this->getRedirectLocation('/')->shouldBe('http://test.test');
    }
}
