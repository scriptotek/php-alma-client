<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class HoldingSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $holdings_id = '123';
        $this->beConstructedWith($client, $bib, $holdings_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Holding::class);
    }

    public function it_has_items(AlmaClient $client)
    {
        $client->getJSON('/bibs/abc/holdings/123/items')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('items_response.json'));

        $items = $this->items;
        $items->shouldHaveCount(9);

        $items->rewind();
        $items->valid()->shouldBe(true);
        $items->current()->shouldHaveType(Item::class);
    }
}
