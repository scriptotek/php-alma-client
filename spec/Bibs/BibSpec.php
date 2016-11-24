<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Bibs;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Holdings;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class BibSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $mms_id = '999104760474702204';
        $this->beConstructedWith($almaClient, $mms_id);

        $almaClient->getXML(Argument::containingString('999104760474702204'), Argument::any())
            ->willReturn(SpecHelper::getDummyData('bib_response_iz.xml'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Bib::class);
    }

    public function it_should_provide_data(AlmaClient $almaClient)
    {
        $this->created_date->shouldBe('2015-11-05Z');
    }

    public function it_links_to_network_zone(AlmaClient $almaClient, AlmaClient $nz, Bibs $bibs, Bib $nz_bib)
    {
        $almaClient->nz = $nz;
        $nz->bibs = $bibs;
        $bibs->get('999104760474702201')
            ->shouldBeCalled()
            ->willReturn($nz_bib);

        $this->getNzRecord()->shouldHaveType(Bib::class);
    }

    public function it_has_holdings(AlmaClient $almaClient, AlmaClient $nz, Bibs $bibs)
    {
        $this->holdings->shouldHaveType(Holdings::class);
    }

    public function it_allows_looking_up_a_single_holding(AlmaClient $almaClient, AlmaClient $nz, Bibs $bibs)
    {
        $this->getHolding('123')->shouldHaveType(Holding::class);
    }
}
