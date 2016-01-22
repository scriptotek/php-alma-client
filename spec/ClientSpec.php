<?php

namespace spec\Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Zones;

function str_random()
{
    return strval(rand(10000,99999));
}

class ClientSpec extends ObjectBehavior
{
    public function let(HttpClient $httpClient)
    {
        $apiKey = 'DummyApiKey';
        $region = 'eu';
        $this->beConstructedWith($apiKey, $region, Zones::INSTITUTION, $httpClient);
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

    public function it_can_do_GET_requests(HttpClient $httpClient, Response $response)
    {
        $dummyResponseText = str_random(); // ... or some xml
        $path = str_random();

        // $xmlElementBuilder->make($dummyResponseText)
        //     ->shouldBeCalled();

        $response->getBody()
            ->shouldBeCalled()
            ->willReturn($dummyResponseText);

        $httpClient->request('GET', Argument::containingString($path), Argument::any())
            ->shouldBeCalled()
            ->willReturn($response);

        $this->getJSON($path);
    }

    public function it_sends_an_API_key_with_each_request(HttpClient $httpClient, Response $response)
    {
        $response->getBody()->willReturn(str_random());

        $httpClient->request('GET', Argument::any(), Argument::containing(
            Argument::withEntry('Authorization', 'apikey DummyApiKey')
        ))->willReturn($response);

        $this->getJSON(str_random());
    }

    public function it_can_request_JSON(HttpClient $httpClient, Response $response)
    {
        $response->getBody()->willReturn(str_random());
        $httpClient->request('GET', Argument::any(), Argument::containing(
            Argument::withEntry('Accept', 'application/json')
        ))->willReturn($response);

        $this->getJSON(str_random());
    }

    public function it_can_request_XML(HttpClient $httpClient, Response $response)
    {
        $dummyResponseText = "<?xml version=\"1.0\"?>\n<bib>" . str_random() . "</bib>\n";
        $response->getBody()->willReturn($dummyResponseText);
        $httpClient->request('GET', Argument::any(), Argument::containing(
            Argument::withEntry('Accept', 'application/xml')
        ))->willReturn($response);

        $this->getXML(str_random());
    }

    public function it_parses_responses_as_XML(HttpClient $httpClient, Response $response)
    {
        $dummyResponseText = "<?xml version=\"1.0\"?>\n<bib>" . str_random() . "</bib>\n";
        $response->getBody()
            ->willReturn($dummyResponseText);
        $httpClient->request(Argument::cetera())
            ->willReturn($response);
        $xml = $this->getXML(str_random());
        $xml->shouldHaveType('Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement');
        $xml->asXML()->shouldBe($dummyResponseText);
    }

    public function it_can_do_PUT_requests(HttpClient $httpClient, Response $response)
    {
        $path = str_random();
        $data = str_random();

        $response->getStatusCode()->willReturn(200);

        $httpClient->request('PUT', Argument::containingString($path), Argument::any())
            ->willReturn($response);

        $result = $this->put($path, $data);
        $result->shouldBe(true);
    }

}
