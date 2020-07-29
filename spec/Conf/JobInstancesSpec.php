<?php

namespace spec\Scriptotek\Alma\Conf;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Job;
use Scriptotek\Alma\Conf\JobInstance;
use spec\Scriptotek\Alma\SpecHelper;

class JobInstancesSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Job $job)
    {
        $job->job_id = '1108569450000121';
        $this->beConstructedWith($client, $job);
    }

    public function it_provides_a_lazy_interface_to_jobinstance_objects(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);

        $jobId = '1108569450000121';
        $instanceId = '1108569450000121';
        $jobInstance = $this->get($jobId, $instanceId);
        $jobInstance->shouldBeAnInstanceOf(JobInstance::class);
    }

    public function it_provides_jobintances(AlmaClient $client)
    {
        $jobId = '1108569450000121';
        $client->getJSON("/conf/jobs/{$jobId}/instances")
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('jobinstances_response.json'));

        $this->all()->shouldBeArray();
        $this->all()[0]->shouldBeAnInstanceOf(JobInstance::class);
    }
}
