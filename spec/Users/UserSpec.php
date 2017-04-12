<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\User;
use spec\Scriptotek\Alma\SpecHelper;

class UserSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient, '123456');

        $almaClient->getJSON(Argument::containingString('12345'), Argument::any())
            ->willReturn(SpecHelper::getDummyData('user_response.json'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }

    public function it_has_identifiers()
    {
        $this->primary_id->shouldBe('12345');
        $this->getUniversityId()->shouldBe('test@uio.no');
        $this->getBarcode()->shouldBe(null);
    }
}
