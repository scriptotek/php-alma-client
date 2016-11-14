<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Users\User;
use Scriptotek\Alma\Users\Users;
use Scriptotek\Alma\Client as AlmaClient;

class UsersSpec extends ObjectBehavior
{
    public function it_is_initializable(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);
        $this->shouldHaveType(Users::class);
    }

    public function it_provides_an_interface_to_user_objects(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);

        $user_id = '123'; // str_random();
        $user = $this->get($user_id);

        $user->shouldHaveType(User::class);
        $user->primary_id->shouldBe($user_id);
    }
}
