<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Fees;
use Scriptotek\Alma\Users\Loans;
use Scriptotek\Alma\Users\Requests;
use Scriptotek\Alma\Users\User;
use spec\Scriptotek\Alma\SpecHelper;

class UserSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $this->beConstructedWith($client, '12345');

        $client->getJSON('/users/12345')
            ->willReturn(SpecHelper::getDummyData('user_response.json'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }

    public function it_has_primary_id()
    {
        $this->primaryId->shouldBe('12345');
    }

    public function it_has_barcode()
    {
        $this->barcode->shouldBe('ub54321');
    }

    public function it_has_barcodes()
    {
        $this->barcodes->shouldBe(['ub54321', 'ntb12897787']);
    }

    public function it_has_university_id()
    {
        $this->universityId->shouldBe('test@uio.no');
    }

    public function it_has_university_ids()
    {
        $this->universityIds->shouldBe(['test@uio.no']);
    }

    public function it_has_identifiers()
    {
        $this->identifiers->all()->shouldBe(['12345', 'ub54321', 'ntb12897787', 'test@uio.no']);
    }

    public function it_has_loans(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);
        $this->loans->shouldHaveType(Loans::class);
    }

    public function it_has_fees(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);
        $this->fees->shouldHaveType(Fees::class);
    }

    public function it_has_requests()
    {
        $this->requests->shouldHaveType(Requests::class);
    }

    public function it_has_sms()
    {
        $this->getSmsNumber()->shouldBe('87654321');
    }
    
    public function it_can_change_sms()
    {
        $this->setSmsNumber('12345678');
        $this->getSmsNumber()->shouldBe('12345678');
    }

    public function it_can_add_sms()
    {
        $this->setSmsNumber('9999999');
        $this->getSmsNumber()->shouldBe('9999999');
    }

    public function it_can_remove_sms()
    {
        $this->unsetSmsNumber();
        $this->getSmsNumber()->shouldBe(null);
    }

}
