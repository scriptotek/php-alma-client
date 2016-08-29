<?php

namespace spec\Scriptotek\Alma;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;

class AnalyticsSpec extends ObjectBehavior
{

    public function let(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\Analytics');
    }

    public function it_provides_an_array_interface_to_report_objects(AlmaClient $almaClient)
    {
        $path = 'UIO,Universitetsbiblioteket/Reports/RSS/Nyhetslister : Fransk';  // str_random();

        $report = $this[$path];

        $this->shouldImplement('ArrayAccess');
        $report->shouldHaveType('Scriptotek\Alma\Models\Report');
        $report->path->shouldBe($path);
    }

}
