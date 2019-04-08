<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Portfolios;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class PortfoliosSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $this->beConstructedWith($client, $bib);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Portfolios::class);
    }

    protected function expectRequest($client)
    {
        $client->getJSON('/bibs/abc/portfolios')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('portfolios_response.json'));
    }

    public function it_is_countable(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->shouldHaveCount(1);
    }
}
