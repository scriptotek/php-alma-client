<?php

namespace spec\Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;

class SpecHelper {

    static public function getDummyData($filename, $parse=true)
    {
        $data = file_get_contents(__DIR__ . '/data/' . $filename);
        return $parse ? QuiteSimpleXMLElement::make($data) : $data;
    }
}
