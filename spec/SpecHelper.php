<?php

namespace spec\Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;

class SpecHelper
{
    public static function getDummyData($filename, $parse = true)
    {
        $data = file_get_contents(__DIR__ . '/data/' . $filename);

        return $parse ? QuiteSimpleXMLElement::make($data) : $data;
    }
}
