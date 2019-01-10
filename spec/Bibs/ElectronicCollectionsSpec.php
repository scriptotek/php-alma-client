<?php

namespace spec\Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\ElectronicCollections;
use Scriptotek\Alma\Client as AlmaClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Electronic\Collection;
use spec\Scriptotek\Alma\SpecHelper;

class ElectronicCollectionsSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $this->beConstructedWith($client, $bib);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ElectronicCollections::class);
    }

    protected function expectRequest($client)
    {
        $client->getJSON('/bibs/abc/e-collections')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('e-collections_response.json'));
    }

    public function it_is_countable(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->shouldHaveCount(1);
    }

    public function it_provides_basic_data_without_loading_the_full_record(AlmaClient $client)
    {
        $this->expectRequest($client);

        $c = $this->all()[0];

        $c->shouldHaveType(Collection::class);
        $c->public_name->shouldBe('SpringerLink Books Complete');
    }
}
