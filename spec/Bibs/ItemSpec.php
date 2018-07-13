<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class ItemSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $mms_id = 'abc';
        $holdings_id = '123';
        $item_id = '99991';
        $this->beConstructedWith($almaClient, $mms_id, $holdings_id, $item_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Item::class);
    }
}
