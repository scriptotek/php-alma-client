<?php

namespace spec\Scriptotek\Alma\Conf;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Job;
use spec\Scriptotek\Alma\SpecHelper;

class JobsSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $this->beConstructedWith($client);
    }

    public function it_provides_a_lazy_interface_to_job_objects(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);

        $job_id = 'M26714670000011';
        $job = $this->get($job_id);

        $job->shouldBeAnInstanceOf(Job::class);
        $job->job_id->shouldBe($job_id);
    }

    public function it_provides_jobs(AlmaClient $client)
    {
        $client->getJSON(Argument::containingString('/conf/jobs?'))
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('jobs_response.json'));

        $this->all()->shouldBeArray();
        $this->all()[0]->shouldBeAnInstanceOf(Job::class);
    }
}
