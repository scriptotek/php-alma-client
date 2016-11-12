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
