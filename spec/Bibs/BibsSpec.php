<?php

namespace spec\Scriptotek\Alma\Bibs;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Bibs;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Sru\Client as SruClient;
use Scriptotek\Sru\Record as SruRecord;
use spec\Scriptotek\Alma\SpecHelper;

class BibsSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient, SruClient $sru)
    {
        $this->beConstructedWith($almaClient);
        $almaClient->sru = $sru;
    }

    public function it_is_initializable(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);
        $this->shouldHaveType(Bibs::class);
    }

    public function it_provides_an_interface_to_bib_objects()
    {
        $mms_id = '123'; // str_random();
        $bib = $this->get($mms_id);

        $bib->shouldHaveType(Bib::class);
        $bib->mms_id->shouldBe($mms_id);
    }

    public function it_returns_a_bib_object_given_an_isbn(SruClient $sru)
    {
        $sru->all('alma.isbn="123"', 1)
            ->shouldBeCalled()
            ->willReturn([SruRecord::make(1,
                '<record><controlfield tag="001">990114012304702201</controlfield></record>'
                )]);

        $bib = $this->fromIsbn('123');
        $bib->shouldHaveType(Bib::class);
        $bib->mms_id->shouldBe('990114012304702201');
    }

    public function it_returns_null_given_unknown_isbn(SruClient $sru)
    {
        $sru->all('alma.isbn="123"', 1)
            ->shouldBeCalled()
            ->willReturn([]);

        $bib = $this->fromIsbn('123');
        $bib->shouldBe(null);
    }

    public function it_supports_lookup_by_holding_id(AlmaClient $almaClient)
    {
        $almaClient->getXML('/bibs', Argument::containing('12345'), Argument::any())
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('bibs_holdings.xml'));

        $bib = $this->fromHoldingsId('12345');
        $bib->shouldHaveType(Bib::class);
        $bib->mms_id->shouldBe('999900137074702204');
    }

    /*
    public function it_returns_a_bib_object_given_a_barcode(AlmaClient $almaClient)
    {
    }
    */
}
