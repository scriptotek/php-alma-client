<?php

namespace spec\Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Prophecy\Argument;

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
}
