<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Bibs;
use Scriptotek\Alma\Bibs\Holdings;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Exception\ResourceNotFound;
use Scriptotek\Alma\Users\Requests;
use Scriptotek\Marc\Record;
use spec\Scriptotek\Alma\SpecHelper;

class BibSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $this->beConstructedWith($client, '999104760474702204');
    }

    protected function expectRequest($client)
    {
        $client->getXML('/bibs/999104760474702204')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('bib_response_iz.xml'));
    }

    public function it_is_lazy(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);
        $this->shouldHaveType(Bib::class);
    }

    public function it_loads_bib_data_when_needed(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->created_date->shouldBe('2015-11-05Z');
    }

    public function it_can_exist(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->exists()->shouldBe(true);
    }

    public function it_links_to_network_zone(AlmaClient $client, AlmaClient $nz, Bibs $bibs, Bib $nz_bib)
    {
        $this->expectRequest($client);

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

    public function it_has_a_MARC_record(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->record->shouldHaveType(Record::class);
        $this->record->getField('245')->getSubfield('a')->getData()->shouldBe('Lonely hearts of the cosmos :');
    }

    public function it_can_be_edited(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->record->getField('245')->getSubfield('a')->setData('New title');

        $client->putXML('/bibs/999104760474702204', Argument::containingString('New title'))
            ->shouldBeCalled();

        $this->save();
    }

    public function it_catches_resource_not_found(AlmaClient $client)
    {
        $client->getXML('/bibs/999104760474702204')
            ->shouldBeCalled()
            ->willThrow(ResourceNotFound::class);

        $this->exists()->shouldBe(false);
    }

    public function it_has_requests()
    {
        $this->requests->shouldHaveType(Requests::class);
    }
}
