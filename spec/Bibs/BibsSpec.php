<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Marc\Record;
use Scriptotek\Sru\Client as SruClient;
use Scriptotek\Sru\Record as SruRecord;
use spec\Scriptotek\Alma\SpecHelper;

class BibsSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, SruClient $sru)
    {
        $this->beConstructedWith($client);
        $client->sru = $sru;
    }

    public function it_provides_a_lazy_interface_to_bib_objects(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);

        $mms_id = '123'; // str_random();
        $bib = $this->get($mms_id);

        $bib->shouldHaveType(Bib::class);
        $bib->mms_id->shouldBe($mms_id);
    }

    public function it_provides_a_lazy_array_interface_to_bib_objects(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);

        $mms_id = '123'; // str_random();
        $bib = $this[$mms_id];

        $bib->shouldHaveType(Bib::class);
        $bib->mms_id->shouldBe($mms_id);
    }

    public function it_accepts_expand_parameter(AlmaClient $client)
    {
        $client->getXML('/bibs/12345?expand=p_avail')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('bib_response_with_availability.xml'));

        $this->get('12345', 'p_avail')->record;
    }

    public function it_provides_lookup_by_isbn(AlmaClient $client, SruClient $sru)
    {
        SpecHelper::expectNoRequests($client);
        $client->assertHasSruClient()->shouldBeCalled()->willReturn(true);

        $sru->all('alma.isbn="123"', 1)
            ->shouldBeCalled()
            ->willReturn([SruRecord::make(
                1,
                '<record xmlns="http://www.loc.gov/MARC21/slim"><controlfield tag="001">990114012304702201</controlfield></record>'
            )]);

        $bib = $this->fromIsbn('123');
        $bib->shouldHaveType(Bib::class);

        // This operation should be lazy
        $bib->mms_id->shouldBe('990114012304702201');

        // This operation should also be lazy
        $bib->record->shouldBeAnInstanceOf(Record::class);
    }

    public function it_returns_null_given_unknown_isbn(AlmaClient $client, SruClient $sru)
    {
        SpecHelper::expectNoRequests($client);
        $client->assertHasSruClient()->shouldBeCalled()->willReturn(true);

        $sru->all('alma.isbn="123"', 1)
            ->shouldBeCalled()
            ->willReturn([]);

        $bib = $this->fromIsbn('123');
        $bib->shouldBe(null);
    }

    public function it_supports_lookup_by_holding_id(AlmaClient $client)
    {
        $client->getXML('/bibs', Argument::containing('12345'))
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('bibs_holdings.xml'));

        $bib = $this->fromHoldingsId('12345');
        $bib->shouldHaveType(Bib::class);
        $bib->mms_id->shouldBe('999900137074702204');
    }

    /*
    public function it_returns_a_bib_object_given_a_barcode(AlmaClient $client)
    {
    }
    */
}
