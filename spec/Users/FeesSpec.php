<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Fee;
use Scriptotek\Alma\Users\Fees;
use Scriptotek\Alma\Users\User;
use spec\Scriptotek\Alma\SpecHelper;

class FeesSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, User $user)
    {
        $user->id = '123435';
        $this->beConstructedWith($client, $user);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Fees::class);
    }

    public function it_yields_fees(AlmaClient $client)
    {
        $client->getJSON('/users/123435/fees')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('fees_response.json'));

        $this->total_sum->shouldBe(750);

        $this->rewind();
        $this->valid()->shouldBe(true);
        $this->current()->shouldBeAnInstanceOf(Fee::class);

        $this->shouldHaveCount(1);
    }

    public function it_can_be_empty(AlmaClient $client)
    {
        $client->getJSON('/users/123435/fees')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('zero_fees_response.json'));

        $this->rewind();
        $this->valid()->shouldBe(false);

        $this->shouldHaveCount(0);
    }

}
