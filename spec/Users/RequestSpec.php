<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Request;
use Scriptotek\Alma\Users\User;

class RequestSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, User $user)
    {
        $this->beConstructedWith($client, $user, '123');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Request::class);
    }
}
