<?php

namespace spec\Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Xml\QuiteSimpleXmlElementBuilder;

class ClientSpec extends ObjectBehavior
{
    public function let(QuiteSimpleXmlElementBuilder $xmlElementBuilder, HttpClient $httpClient)
    {
        $apiKey = 'DummyApiKey';
        $region = 'eu';
        $this->beConstructedWith($apiKey, $region, $xmlElementBuilder, $httpClient);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\Client');
    }

    public function it_only_accepts_valid_regions()
    {
        $this->shouldThrow('\ErrorException')
            ->duringSetRegion('ux');
    }

    public function it_can_do_GET_requests(HttpClient $httpClient, Response $response, QuiteSimpleXmlElementBuilder $xmlElementBuilder, QuiteSimpleXMLElement $xmlElement)
    {
        $dummyResponseText = str_random(); // ... or some xml
        $path = str_random();

        $xmlElementBuilder->make($dummyResponseText)
            ->shouldBeCalled();

        $response->getBody()
            ->shouldBeCalled()
            ->willReturn($dummyResponseText);

        $httpClient->request('GET', Argument::containingString($path), Argument::any())
            ->shouldBeCalled()
            ->willReturn($response);

        $this->get($path);
    }

    public function it_sends_an_API_key_with_each_request(HttpClient $httpClient, Response $response)
    {
        $response->getBody()->willReturn(str_random());
        $httpClient->request('GET', Argument::any(), Argument::containing(['apikey' => 'DummyApiKey']))
            ->willReturn($response);

        $this->get(str_random());
    }

    public function it_requests_XML(HttpClient $httpClient, Response $response)
    {
        $response->getBody()->willReturn(str_random());
        $httpClient->request('GET', Argument::any(), Argument::containing(['Accept' => 'application/xml']))
            ->willReturn($response);

        $this->get(str_random());
    }

    public function it_parses_responses_as_XML(HttpClient $httpClient, Response $response, QuiteSimpleXmlElementBuilder $xmlElementBuilder, QuiteSimpleXMLElement $xmlElement)
    {
        $dummyResponseText = str_random();
        $xmlElementBuilder->make($dummyResponseText)->willReturn($xmlElement);
        $response->getBody()->willReturn($dummyResponseText);
        $httpClient->request(Argument::cetera())->willReturn($response);
        $xml = $this->get(str_random());

        $xml->shouldBe($xmlElement);
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
