<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Request;
use Scriptotek\Alma\Users\User;

class RequestSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, User $user)
    {
        $this->beConstructedWith($client, $user, '123');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Request::class);
    }
}
