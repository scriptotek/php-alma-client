<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\UriInterface;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Bibs;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Holdings;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Exception\ResourceNotFound;
use Scriptotek\Marc\Record;
use spec\Scriptotek\Alma\SpecHelper;

class BibSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, UriInterface $url)
    {
        $mms_id = '999104760474702204';
        $this->beConstructedWith($client, $mms_id);
    }

    protected function expectRequest($client, $url) {
        $client->buildUrl('/bibs/999104760474702204', [])
            ->shouldBeCalled()
            ->willReturn($url);

        $client->getJSON($url)
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('bib_response_iz.json'));
    }

    public function it_is_lazy(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);
        $this->shouldHaveType(Bib::class);
    }

    public function it_loads_bib_data_when_needed(AlmaClient $client, UriInterface $url)
    {
        $this->expectRequest($client, $url);

        $this->created_date->shouldBe('2015-11-05Z');
    }

    public function it_loads_bib_data_when_needed2(AlmaClient $client, UriInterface $url)
    {
        $this->expectRequest($client, $url);

        $this->exists()->shouldBe(true);
    }

    public function it_links_to_network_zone(AlmaClient $client, AlmaClient $nz, Bibs $bibs, Bib $nz_bib, UriInterface $url)
    {
        $this->expectRequest($client, $url);

        $client->nz = $nz;
        $nz->bibs = $bibs;
        $bibs->get('999104760474702201')
            ->shouldBeCalled()
            ->willReturn($nz_bib);

        $this->getNzRecord()->shouldHaveType(Bib::class);
    }

    public function it_provides_lazy_access_to_holdings(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);
        $this->holdings->shouldHaveType(Holdings::class);
    }

    public function it_has_a_MARC_record(AlmaClient $client, UriInterface $url)
    {
        $this->expectRequest($client, $url);

        $this->record->shouldHaveType(Record::class);
        $this->record->getField('245')->getSubfield('a')->getData()->shouldBe('Lonely hearts of the cosmos :');
    }

    public function it_can_be_edited(AlmaClient $client, UriInterface $url)
    {
        $this->expectRequest($client, $url);

        $this->record->getField('245')->getSubfield('a')->setData('New title');

        $client->putJSON('/bibs/999104760474702204', Argument::containingString('New title'))
            ->shouldBeCalled();
        $this->save();
    }

    public function it_catches_resource_not_found(AlmaClient $client, UriInterface $url)
    {
        $client->buildUrl('/bibs/999104760474702204', [])
            ->shouldBeCalled()
            ->willReturn($url);

        $client->getJSON($url)
            ->shouldBeCalled()
            ->willThrow(ResourceNotFound::class);

        $this->exists()->shouldBe(false);
    }
}
