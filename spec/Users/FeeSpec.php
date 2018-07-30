<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\UriInterface;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Fee;
use Scriptotek\Alma\Users\User;
use spec\Scriptotek\Alma\SpecHelper;


class FeeSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, User $user)
    {
        $fee_id = '123435';
        $this->beConstructedWith($client, $user, $fee_id);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Fee::class);
    }

    public function it_can_exist()
    {
        $this->init(SpecHelper::getDummyData('fee_response.json'));

        $this->exists()->shouldBe(true);
        $this->balance->shouldBe(750);
    }
}
