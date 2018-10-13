<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Loan;
use Scriptotek\Alma\Users\User;
use spec\Scriptotek\Alma\SpecHelper;

class LoanSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, User $user, Item $item)
    {
        $loan_id = '123';
        $this->beConstructedWith($client, $user, $item, $loan_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Loan::class);
    }

    public function it_can_exist()
    {
        $this->init(SpecHelper::getDummyData('item_loan_response.json')->item_loan[0]);

        $this->exists()->shouldBe(true);
    }
}
