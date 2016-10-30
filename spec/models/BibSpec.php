<?php

namespace spec\Scriptotek\Alma\models;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Bibs;
use Scriptotek\Alma\Models\Bib;
use spec\Scriptotek\Alma\SpecHelper;

class BibSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $mms_id = '999104760474702204';
        $this->beConstructedWith($almaClient, $mms_id);

        $almaClient->getXML(Argument::containingString('999104760474702204'))
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('bib_response_iz.xml'));

        $this->fetch();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\models\Bib');
    }

    // public function it_has_holdings(AlmaClient $almaClient)
    // {
    //     $this->holdings();
    // }

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

        $this->getNzRecord()->shouldHaveType('Scriptotek\Alma\models\Bib');
    }

}
