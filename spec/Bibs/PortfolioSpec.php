<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Portfolio;
use Scriptotek\Alma\Bibs\Portfolios;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Electronic\Collection;
use spec\Scriptotek\Alma\SpecHelper;

class PortfolioSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $portfolio_id = '123';
        $this->beConstructedWith($client, $bib, $portfolio_id);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Portfolio::class);
    }

    protected function expectRequest($client)
    {
        $client->getJSON('/bibs/abc/portfolios/123')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('portfolio_response.json'));
    }

    public function it_fetches_data_when_needed(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->availability->desc->shouldBe('Available');
    }

    public function it_belongs_to_collection(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->getElectronicCollection()->shouldHaveType(Collection::class);
        $this->electronic_collection->shouldHaveType(Collection::class);
    }}
