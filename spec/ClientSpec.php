<?php

namespace spec\Scriptotek\Alma;

use GuzzleHttp\Psr7\Response;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Exception\ServerErrorException;
use Http\Mock\Client as MockHttp;
use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Exception\InvalidApiKey;
use Scriptotek\Alma\Exception\RequestFailed;
use Scriptotek\Alma\Exception\ResourceNotFound;
use Scriptotek\Alma\Zones;

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

    protected function httpWithResponseBody($body, $statusCode = 200, $headers = [])
    {
        $http = $this->let();
        $http->addResponse(new Response($statusCode, $headers, $body));

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

    public function it_can_post_and_parse_JSON()
    {
        $http = $this->httpWithResponseBody(SpecHelper::getDummyData('create_loan_response.json', false));

        $this->postJSON(str_random())->loan_id->shouldBe('7329587120002204');

        $request = $http->getRequests()[0];
        expect($request->getHeader('Content-Type')[0])->toBe('application/json');
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

        $this->put(str_random(), str_random(), 'application/json')->shouldBe($responseBody);

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

    public function it_processes_json_error_responses(ClientErrorException $exception)
    {
        $exception->getResponse()->willReturn(new Response(
            400,
            ['Content-Type' => 'application/json;charset=utf-8'],
            SpecHelper::getDummyData('error_response.json', false)
        ));

        $http = $this->let();
        $http->addException($exception->getWrappedObject());

        $this->shouldThrow(new RequestFailed(
            'Mandatory field is missing: library',
            '401664'
        ))->during('getJSON', ['/items/123']);

        expect($http->getRequests())->toHaveCount(1);
    }

    public function it_processes_xml_error_responses(ClientErrorException $exception)
    {
        $exception->getResponse()->willReturn(new Response(
            400,
            ['Content-Type' => 'application/xml;charset=utf-8'],
            SpecHelper::getDummyData('error_response.xml', false)
        ));

        $http = $this->let();
        $http->addException($exception->getWrappedObject());

        $this->shouldThrow(new RequestFailed(
            'Mandatory field is missing: library',
            '401664'
        ))->during('getXML', ['/items/123']);

        expect($http->getRequests())->toHaveCount(1);
    }

    public function it_can_throw_resource_not_found(ClientErrorException $exception)
    {
        $exception->getResponse()->willReturn(new Response(
            400,
            ['Content-Type' => 'application/json;charset=utf-8'],
            SpecHelper::getDummyData('item_barcode_error_response.json', false)
        ));

        $http = $this->let();
        $http->addException($exception->getWrappedObject());

        $this->shouldThrow(new ResourceNotFound('No items found for barcode 123.', '401689'))
            ->during('getJSON', ['/items/123']);

        expect($http->getRequests())->toHaveCount(1);
    }

    public function it_can_throw_resource_not_found_for_500_errors_too(ServerErrorException $exception)
    {
        // For Analytics reports, Alma will return 500, not 4xx
        $exception->getResponse()->willReturn(new Response(
            500,
            ['Content-Type' => 'application/xml;charset=utf-8'],
            SpecHelper::getDummyData('report_not_found_response.xml', false)
        ));

        $http = $this->let();
        $http->addException($exception->getWrappedObject());

        $this->shouldThrow(new ResourceNotFound('Path not found (/test/path)', 'INTERNAL_SERVER_ERROR'))
            ->during('getXML', ['/analytics/reports', ['path' => '/test/path']]);

        expect($http->getRequests())->toHaveCount(1);
    }

    public function it_can_throw_invalid_api_key(ClientErrorException $exception)
    {
        $exception->getResponse()->willReturn(new Response(
            400,
            ['Content-Type' => 'text/plain;charset=UTF-8'],
            'Invalid API Key'
        ));

        $http = $this->let();
        $http->addException($exception->getWrappedObject());

        $this->shouldThrow(new InvalidApiKey('Invalid API Key', 0))
           ->during('getJSON', ['/items/123']);

        expect($http->getRequests())->toHaveCount(1);
    }

    public function it_will_retry_when_reaching_rate_limit(ClientErrorException $exception)
    {
        $exception->getResponse()->willReturn(new Response(
            400,
            ['Content-Type' => 'application/json;charset=utf-8'],
            SpecHelper::getDummyData('per_second_threshold_error_response.json', false)
        ));

        $http = $this->let();
        $http->addException($exception->getWrappedObject());

        $this->getJSON('/items/123');

        expect($http->getRequests())->toHaveCount(2);
    }
}
