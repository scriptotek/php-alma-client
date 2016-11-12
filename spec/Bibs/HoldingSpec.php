<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class HoldingSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $mms_id = 'abc';
        $holdings_id = '123';
        $this->beConstructedWith($almaClient, $mms_id, $holdings_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Holding::class);
    }

    public function it_has_items(AlmaClient $almaClient)
    {
        $almaClient->getJSON(Argument::containingString('123'))
            ->willReturn(SpecHelper::getDummyData('items_response.json'));

        $items = $this->items;
        $items->shouldHaveCount(9);
        $items[0]->shouldHaveType(Item::class);
    }
}
