<?php

namespace spec\Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\ResponseFactory;
use Prophecy\Argument;
use function GuzzleHttp\Psr7\stream_for;

class SpecHelper
{
    public static function getDummyData($filename, $parse = true)
    {
        $data = file_get_contents(__DIR__ . '/data/' . $filename);

        if (!$parse) {
            return $data;
        }

        if (strpos($filename, '.xml')) {
            return QuiteSimpleXMLElement::make($data);
        }

        return json_decode($data);
    }

    public static function expectNoRequests($client)
    {
        $client->getJSON(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $client->getXML(Argument::any(), Argument::any())
            ->shouldNotBeCalled();
    }

    public static function makeExceptionResponse(
        $body,
        $code = 400,
        $contentType = 'application/json;charset=utf-8',
        $cls = ClientErrorException::class
    ) {
        $requestFactory = new RequestFactory();
        $responseFactory = new ResponseFactory();

        return new $cls(
            'Error 400',
            $requestFactory->createRequest('GET', ''),
            $responseFactory->createResponse($code, 'Bad Request')
                ->withHeader('Content-Type', $contentType)
                ->withBody(stream_for($body))
        );
    }
}
