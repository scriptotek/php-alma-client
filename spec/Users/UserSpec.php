<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Users\User;
use Scriptotek\Alma\Users\Users;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class UserSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient, '123456', []);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }

    public function it_fetches_data_only_when_needed(AlmaClient $almaClient)
    {
        $almaClient->getJSON(Argument::containingString('12345'), Argument::any())
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('user_response.json'));

        $this->primary_id->shouldBe('12345');
    }

}
