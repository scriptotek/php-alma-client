<?php

namespace spec\Scriptotek\Alma\Analytics;

use PhpSpec\ObjectBehavior;
use Psr\Http\Message\UriInterface;
use Scriptotek\Alma\Analytics\Report;
use Scriptotek\Alma\Analytics\Row;
use Scriptotek\Alma\Analytics\Rows;
use Scriptotek\Alma\Client;
use spec\Scriptotek\Alma\SpecHelper;

class ReportSpec extends ObjectBehavior
{
    public function let(Client $almaClient)
    {
        $this->beConstructedWith($almaClient, 'xyz');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Report::class);
    }

    public function it_supports_setting_headers(Client $almaClient)
    {
        $this->beConstructedWith($almaClient, 'xyz', ['a', 'b']);

        $this->headers->shouldBe(['a', 'b']);
    }

    public function it_supports_setting_filter(Client $almaClient)
    {
        $this->beConstructedWith($almaClient, 'xyz', ['a', 'b'], 'la la la');

        $this->filter->shouldBe('la la la');
    }


    public function it_can_be_counted(Client $almaClient, UriInterface $url)
    {
        $almaClient->buildUrl('/analytics/reports', [
            'path' => 'xyz',
            'limit' => 1000,
            'token' => null,
            'filter' => null,
        ])->shouldBeCalled()->willReturn($url);

        $almaClient->getXML($url)
            ->shouldBeCalledTimes(1)
            ->willReturn(SpecHelper::getDummyData('analytics_response.xml'));

        $this->shouldHaveCount(14);
    }

    public function it_supports_resumption(Client $almaClient, UriInterface $url1, UriInterface $url2)
    {
        $path = 'xyz';

        // To speed up tests
        Report::$retryDelayTime = 0;

        $almaClient->buildUrl('/analytics/reports', [
            'path' => $path,
            'limit' => 1000,
            'token' => null,
            'filter' => null,
        ])->shouldBeCalled()->willReturn($url1);

        $almaClient->getXML($url1)
            ->shouldBeCalledTimes(1)
            ->willReturn(SpecHelper::getDummyData('analytics_response_part1.xml'));

        $almaClient->buildUrl('/analytics/reports', [
            'path' => null,
            'limit' => 1000,
            'token' => '9672D715A8E2EAAA6F30DD22FC52BE4CCAE35E29D921E0AC8BE8C6734C9E1571B4E48EEFCA4046EFF8CD7D1662C2D0A7677D3AD05EDC3CA7F06182E34E9D7A2F',
            'filter' => null,
        ])->shouldBeCalled()->willReturn($url2);

        $almaClient->getXML($url2)
            ->shouldBeCalledTimes(3)
            ->willReturn(

            // If Analytics is having a bad day, we might get a "still loading" response
            // See: https://bitbucket.org/uwlib/uwlib-alma-analytic-tools/wiki/Understanding_Analytic_GET_Requests#!analytic-still-loading
                SpecHelper::getDummyData('analytics_still_loading_response.xml'),

                SpecHelper::getDummyData('analytics_response_part2.xml'),
                SpecHelper::getDummyData('analytics_response_part3.xml')
            );

        count($this->getWrappedObject());
//        $this->shouldHaveCount(150 + 150 + 88);
    }

}
