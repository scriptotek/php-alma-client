<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\UriInterface;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Bibs\Items;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Sru\Client as SruClient;
use spec\Scriptotek\Alma\SpecHelper;

class ItemsSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, SruClient $sru, Bib $bib, Holding $holding)
    {
        $this->beConstructedWith($client, $bib, $holding);
        $client->sru = $sru;
    }

    public function it_is_initializable(AlmaClient $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType(Items::class);
    }

    public function it_returns_a_lazy_loaded_item_object_given_a_barcode(AlmaClient $client, UriInterface $url)
    {
        $client->getRedirectLocation('/items', Argument::containing('303011kj0'))
            ->shouldBeCalled()
            ->willReturn('https://api-eu.hosted.exlibrisgroup.com/almaws/v1/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204');

        $client->buildUrl('/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204', [])
            ->shouldNotBeCalled();

        $item = $this->fromBarcode('303011kj0');
        $item->shouldHaveType(Item::class);
    }

    public function it_returns_an_item_object_given_a_barcode(AlmaClient $client, UriInterface $url)
    {
        $client->getRedirectLocation('/items', Argument::containing('303011kj0'))
            ->shouldBeCalled()
            ->willReturn('https://api-eu.hosted.exlibrisgroup.com/almaws/v1/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204');

        $client->buildUrl('/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204', [])
            ->shouldBeCalled()
            ->willReturn($url);

        $client->getJSON($url)
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('item_response.json'));

        $item = $this->fromBarcode('303011kj0');
        $item->shouldHaveType(Item::class);
        $item->pid->shouldBe('23163771190002204');
    }
}
