<?php

namespace spec\Scriptotek\Alma\models;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class BibSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $mms_id = '990114012304702204';
        $this->beConstructedWith($mms_id, $almaClient);
        $almaClient->getXML(Argument::containingString('990114012304702204'))
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('bib_response.xml'));
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
        $this->created_date->shouldBe('2015-02-10Z');
    }
}
