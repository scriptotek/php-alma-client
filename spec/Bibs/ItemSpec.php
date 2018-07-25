<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Psr\Http\Message\UriInterface;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class ItemSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib, Holding $holding)
    {
        $bib->mms_id = '990006312214702204';
        $holding->holding_id = '22163771200002204';
        $item_id = '23163771190002204';

        $this->beConstructedWith($client, $bib, $holding, $item_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Item::class);
    }
}
