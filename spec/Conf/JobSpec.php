<?php

namespace spec\Scriptotek\Alma\Conf;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Job;
use spec\Scriptotek\Alma\SpecHelper;

class JobSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $this->beConstructedWith($client, 'M26714670000011');

        $client->getJSON('/conf/jobs/M26714670000011')
            ->willReturn(SpecHelper::getDummyData('job_response.json'));
        $client->postJSON('/conf/jobs/M26714670000011?op=run')
            ->willReturn(SpecHelper::getDummyData('job_response.json'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Job::class);
    }
}
