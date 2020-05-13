<?php

namespace spec\Scriptotek\Alma\Analytics;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Analytics\Report;
use Scriptotek\Alma\Analytics\Row;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Exception\ResourceNotFound;
use spec\Scriptotek\Alma\SpecHelper;

class ReportSpec extends ObjectBehavior
{
    public function let(Client $almaClient)
    {
        $this->beConstructedWith($almaClient, '/test/path');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Report::class);
    }

    public function it_supports_setting_headers(Client $almaClient)
    {
        $this->beConstructedWith($almaClient, '/test/path', ['a', 'b']);

        $this->headers->shouldBe(['a', 'b']);
    }

    public function it_supports_setting_filter(Client $almaClient)
    {
        $this->beConstructedWith($almaClient, '/test/path', ['a', 'b'], 'la la la');

        $this->filter->shouldBe('la la la');
    }

    public function it_parses_column_headers(Client $almaClient)
    {
        $almaClient->getXML('/analytics/reports?path=%2Ftest%2Fpath&limit=1000')
            ->shouldBeCalledTimes(1)
            ->willReturn(SpecHelper::getDummyData('analytics_response.xml'));

        // $this->getHeaders()->shouldHaveCount(11);
        $this->getHeaders()->shouldContain('Event Start Date and Time');

        $firstRow = $this->current();
        $firstRow->shouldHaveType(Row::class);
        $firstRow['Event Start Date and Time']->shouldBe('2017-08-29T15:43:53');
    }

    public function it_supports_resumption(Client $almaClient)
    {
        $path = '/test/path';

        // To speed up tests
        Report::$retryDelayTime = 0;

        $almaClient->getXML('/analytics/reports?path=%2Ftest%2Fpath&limit=1000')
            ->shouldBeCalledTimes(1)
            ->willReturn(SpecHelper::getDummyData('analytics_response_part1.xml'));

        $almaClient->getXML('/analytics/reports?limit=1000&token=9672D715A8E2EAAA6F30DD22FC52BE4CCAE35E29D921E0AC8BE8C6734C9E1571B4E48EEFCA4046EFF8CD7D1662C2D0A7677D3AD05EDC3CA7F06182E34E9D7A2F')
            ->shouldBeCalledTimes(3)
            ->willReturn(

                // If Analytics is having a bad day, we might get a "still loading" response
                // See: https://bitbucket.org/uwlib/uwlib-alma-analytic-tools/wiki/Understanding_Analytic_GET_Requests#!analytic-still-loading
                SpecHelper::getDummyData('analytics_still_loading_response.xml'),
                SpecHelper::getDummyData('analytics_response_part2.xml'),
                SpecHelper::getDummyData('analytics_response_part3.xml')
            );

        $this->shouldHaveCount(150 + 150 + 88);
    }

    public function it_might_not_exist(Client $almaClient)
    {
        $almaClient->getXML('/analytics/reports?path=%2Ftest%2Fpath&limit=1000')
            ->shouldBeCalledTimes(1)
            ->willThrow(new ResourceNotFound('Path not found (/test/path)'));

        $this->exists()->shouldReturn(false);
    }
}
