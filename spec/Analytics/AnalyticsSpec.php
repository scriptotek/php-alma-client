<?php

namespace spec\Scriptotek\Alma\Analytics;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;

class AnalyticsSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\Analytics\Analytics');
    }

    public function it_provides_an_interface_to_report_objects(AlmaClient $almaClient)
    {
        $path = 'UIO,Universitetsbiblioteket/Reports/RSS/Nyhetslister : Fransk';  // str_random();

        $report = $this->get($path);

        $report->shouldHaveType('Scriptotek\Alma\Analytics\Report');
        $report->path->shouldBe($path);
    }
}
