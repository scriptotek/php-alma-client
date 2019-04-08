<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\User;
use Scriptotek\Alma\Users\Users;
use spec\Scriptotek\Alma\SpecHelper;

class UsersSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $this->beConstructedWith($client);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Users::class);
    }

    public function it_provides_lazy_lookup_by_id(AlmaClient $client)
    {
        $client->getJSON('/users/12345', [])
            ->shouldNotBeCalled();

        $user = $this->get('12345');
        $user->shouldHaveType(User::class);
    }

    public function it_accepts_additional_parameters(AlmaClient $client)
    {
        $client->getJSON('/users/12345?expand=fees')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('user_response.json'));

        $this->get('12345', ['expand' => 'fees'])->init();
    }

    public function it_provides_lookup_by_id(AlmaClient $client)
    {
        $client->getJSON('/users/12345')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('user_response.json'));

        $user = $this->get('12345');

        $user->shouldHaveType(User::class);
        $user->primary_id->shouldBe('12345');
        $user->primaryId->shouldBe('12345');
    }

    public function it_provides_search(AlmaClient $client)
    {
        $client->getJSON(Argument::containingString('users'), Argument::any())
            ->willReturn(SpecHelper::getDummyData('users_response.json'));

        $users = $this->search('last_name~banan');
        $first = $users->current();

        $first->shouldHaveType(User::class);
        $first->primary_id->shouldBe('1234567');
        $first->primaryId->shouldBe('1234567');
    }
}
