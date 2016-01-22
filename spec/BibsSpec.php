<?php

namespace spec\Scriptotek\Alma;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Models\Bib;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Factory;
use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;


class BibsSpec extends ObjectBehavior
{

    public function let(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\Bibs');
    }

    public function it_provides_an_array_interface_to_bib_objects(AlmaClient $almaClient, Bib $bib)
    {
        $almaClient->getXML('/bibs/123')
            ->shouldBeCalled()
            ->willReturn(new QuiteSimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><bib><mms_id>123</mms_id><record><leader>02615cam a22002417u 4500</leader></record></bib>'));

        $mms_id = '123'; // str_random();
        $bib = $this[$mms_id];

        $this->shouldImplement('ArrayAccess');
        $bib->shouldHaveType('Scriptotek\Alma\Models\Bib');
        $bib->mms_id->shouldBe($mms_id);
    }

}
