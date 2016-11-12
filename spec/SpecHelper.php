<?php

namespace spec\Scriptotek\Alma;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;

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
}
