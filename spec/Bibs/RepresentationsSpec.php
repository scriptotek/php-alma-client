<?php

namespace spec\Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Representations;
use Scriptotek\Alma\Client as AlmaClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\Scriptotek\Alma\SpecHelper;

class RepresentationsSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $this->beConstructedWith($client, $bib);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Representations::class);
    }

    protected function expectRequest($client)
    {
        $client->getJSON('/bibs/abc/representations')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('representations_response.json'));
    }

    public function it_is_countable(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->shouldHaveCount(2);
    }
}
