<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Psr\Http\Message\UriInterface;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class ItemSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $mms_id = '990006312214702204';
        $holdings_id = '22163771200002204';
        $item_id = '23163771190002204';

        $this->beConstructedWith($client, $mms_id, $holdings_id, $item_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Item::class);
    }
}
