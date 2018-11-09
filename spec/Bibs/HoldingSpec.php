<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Marc\Record;
use spec\Scriptotek\Alma\SpecHelper;

class HoldingSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $holdings_id = '123';
        $this->beConstructedWith($client, $bib, $holdings_id);
    }

    protected function expectRequest($client)
    {
        $client->getXML('/bibs/abc/holdings/123')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('holding_response.xml'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Holding::class);
    }

    public function it_fetches_record_data_when_needed(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->created_by->shouldBe('import');
        $this->created_date->shouldBe('2015-11-05Z');

        $this->record->shouldHaveType(Record::class);
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
