<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Holdings;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class HoldingsSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $this->beConstructedWith($client, $bib);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Holdings::class);
    }

    protected function expectRequest($client)
    {
        $client->getJSON('/bibs/abc/holdings')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('holdings_response.json'));
    }

    public function it_provides_a_lazy_interface_to_holding_objects(AlmaClient $client, Bib $bib)
    {
        SpecHelper::expectNoRequests($client);

        $holding_id = '12345'; // str_random();
        $holding = $this->get($holding_id);

        $holding->shouldHaveType(Holding::class);
        $holding->bib->shouldBe($bib);
        $holding->holding_id->shouldBe($holding_id);
    }

    public function it_provides_a_lazy_array_interface_to_holding_objects(AlmaClient $client, Bib $bib)
    {
        SpecHelper::expectNoRequests($client);

        $holding_id = '90123'; // str_random();
        $holding = $this[$holding_id];

        $holding->shouldHaveType(Holding::class);
        $holding->bib->shouldBe($bib);
        $holding->holding_id->shouldBe($holding_id);
    }

    public function it_is_countable(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->shouldHaveCount(2);
    }

    public function it_provides_an_iterator_interface_to_holding_objects(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->shouldImplement('Iterator');

        $this->current()->shouldHaveType(Holding::class);
    }

    public function it_provides_basic_data_without_loading_the_full_record(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->shouldHaveCount(2);
        $this->all()[0]->shouldHaveType(Holding::class);
        $this->all()[0]->library->desc->shouldBe('BURNS');
    }
}
