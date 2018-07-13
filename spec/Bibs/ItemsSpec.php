<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Bibs\Items;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Sru\Client as SruClient;
use Scriptotek\Sru\Record as SruRecord;
use spec\Scriptotek\Alma\SpecHelper;

class ItemsSpec extends ObjectBehavior
{
    public function let(AlmaClient $alma, SruClient $sru)
    {
        $this->beConstructedWith($alma);
        $alma->sru = $sru;
    }

    public function it_is_initializable(AlmaClient $alma)
    {
        $this->beConstructedWith($alma);
        $this->shouldHaveType(Items::class);
    }

    public function it_returns_an_item_object_given_a_barcode(AlmaClient $alma)
    {
        $alma->getRedirectLocation('/items', Argument::containing('303011kj0'), Argument::any())
            ->shouldBeCalled()
            ->willReturn('https://api-eu.hosted.exlibrisgroup.com/almaws/v1/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204');

        $alma->getJSON("/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204")
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('item_response.json'));

        $item = $this->fromBarcode('303011kj0');
        $item->shouldHaveType(Item::class);
        $item->pid->shouldBe('23163771190002204');
    }
}
