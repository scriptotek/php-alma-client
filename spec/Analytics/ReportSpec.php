<?php

namespace spec\Scriptotek\Alma\Analytics;

use Scriptotek\Alma\Analytics\Report;
use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Analytics\Row;
use Scriptotek\Alma\Client;
use spec\Scriptotek\Alma\SpecHelper;

class ReportSpec extends ObjectBehavior
{
    public function let(Client $almaClient)
    {
        $path = 'xyz';
        $this->beConstructedWith($almaClient, $path);
        $almaClient->getXML('/analytics/reports', ['path' => $path, 'limit' => 1000, 'token' => null, 'filter' => null])
            ->willReturn(SpecHelper::getDummyData('analytics_response.xml'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Report::class);
    }

    function it_has_rows()
    {
        $this->rows->shouldImplement(\Generator::class);
        $this->rows->current()->shouldBeAnInstanceOf(Row::class);
    }

    function it_supports_setting_headers(Client $almaClient)
    {
        $this->beConstructedWith($almaClient, 'xyz', ['a', 'b']);

        $this->headers->shouldBe(['a', 'b']);
    }

    function it_supports_setting_filter(Client $almaClient)
    {
        $this->beConstructedWith($almaClient, 'xyz', ['a', 'b'], 'la la la');

        $this->filter->shouldBe('la la la');
    }
}
