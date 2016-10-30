<?php

namespace spec\Scriptotek\Alma\Bibs;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Bibs;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Sru\Client as SruClient;
use Scriptotek\Sru\Record as SruRecord;

class BibsSpec extends ObjectBehavior
{
    public function it_is_initializable(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);
        $this->shouldHaveType(Bibs::class);
    }

    public function it_provides_an_interface_to_bib_objects(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);
        $almaClient->getXML('/bibs/123')
            ->shouldBeCalled()
            ->willReturn(new QuiteSimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><bib><mms_id>123</mms_id><record><leader>02615cam a22002417u 4500</leader></record></bib>'));

        $mms_id = '123'; // str_random();
        $bib = $this->get($mms_id);

        $bib->shouldHaveType(Bib::class);
        $bib->mms_id->shouldBe($mms_id);
    }

    public function it_returns_a_bib_object_given_an_isbn(AlmaClient $almaClient, SruClient $sru)
    {
        $this->beConstructedWith($almaClient);
        $almaClient->sru = $sru;

        $sru->first('alma.isbn="123"')
            ->shouldBeCalled()
            ->willReturn(SruRecord::make(1,
                '<record><controlfield tag="001">990114012304702201</controlfield></record>'
                ));

        $almaClient->getXML('/bibs/990114012304702201')
            ->shouldBeCalled()
            ->willReturn(QuiteSimpleXMLElement::make(
                '<bib><mms_id>990114012304702201</mms_id><record><leader>02615cam a22002417u 4500</leader></record></bib>'
                ));

        $bib = $this->fromIsbn('123');
        $bib->shouldHaveType(Bib::class);
        $bib->mms_id->shouldBe('990114012304702201');
    }

    public function it_returns_null_given_unknown_isbn(AlmaClient $almaClient, SruClient $sru)
    {
        $this->beConstructedWith($almaClient);
        $almaClient->sru = $sru;

        $sru->first('alma.isbn="123"')
            ->shouldBeCalled()
            ->willReturn(null);

        $bib = $this->fromIsbn('123');
        $bib->shouldBe(null);
    }

    /*
    public function it_returns_a_bib_object_given_a_barcode(AlmaClient $almaClient)
    {
    }
    */
}
