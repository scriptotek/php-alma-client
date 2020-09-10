<?php

namespace spec\Scriptotek\Alma\Conf;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Job;
use Scriptotek\Alma\Conf\JobInstance;

class JobInstanceSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Job $job)
    {
        $job->job_id = '123';
        $instanceId = '1108569450000121';
        $this->beConstructedWith($client, $job, $instanceId);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(JobInstance::class);
    }

    public function it_should_belong_to_a_job()
    {
        $this->job->shouldBeAnInstanceOf(Job::class);
    }
}