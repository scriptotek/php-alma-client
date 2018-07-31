<?php

namespace spec\Scriptotek\Alma\Users;

use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Loan;
use Scriptotek\Alma\Users\Loans;
use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Users\User;
use spec\Scriptotek\Alma\SpecHelper;

class LoansSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, User $user)
    {
        $user->id = '123435';
        $this->beConstructedWith($client, $user);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Loans::class);
    }

    public function it_yields_loans(AlmaClient $client)
    {
        $client->getJSON('/users/123435/loans?offset=0&limit=10')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('loans_response.json'));

        $this->rewind();
        $this->valid()->shouldBe(true);
        $this->current()->shouldBeAnInstanceOf(Loan::class);

        $this->shouldHaveCount(2);
    }

    public function it_can_be_empty(AlmaClient $client)
    {
        $client->getJSON('/users/123435/loans?offset=0&limit=10')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('zero_loans_response.json'));

        $this->rewind();
        $this->valid()->shouldBe(false);

        $this->shouldHaveCount(0);
    }
}
