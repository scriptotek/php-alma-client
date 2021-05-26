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
        $this->contactInfo->getSmsNumber()->phone_number->shouldBe('87654321');
    }
    
    public function it_can_change_sms()
    {
        $this->contactInfo->setSmsNumber('12345678');
        $this->contactInfo->getSmsNumber()->phone_number->shouldBe('12345678');
    }

    public function it_can_add_sms()
    {
        $this->contactInfo->setSmsNumber('9999999');
        $this->contactInfo->getSmsNumber()->phone_number->shouldBe('9999999');
    }

    public function it_can_remove_sms()
    {
        $this->contactInfo->unsetSmsNumber();
        $this->contactInfo->getSmsNumber()->shouldBe(null);
    }

    public function it_can_add_email()
    {
        $this->contactInfo->addEmail('example@example.com', 'work', true);
        $this->contactInfo->getEmail()->email_address->shouldBe('example@example.com');
    }

    public function it_can_unset_email()
    {
        $this->contactInfo->unsetEmail();
        $this->contactInfo->getEmail()->shouldBe(null);
    }

    public function it_can_remove_email()
    {
        $this->contactInfo->removeEmail('dan@banan.com');
        $this->contactInfo->allEmails()->shouldBe([]);
    }

    public function it_can_add_address()
    {
        $this->contactInfo->addAddress([
            'line1' => '123 Something Blvd.',
            'city' => 'Somewhere',
            'state_province' => 'IL',
            'postal_code' => '12345',
            'country' => 'USA',
            'address_type' => 'home'
        ])->shouldBeAnInstanceOf('Scriptotek\Alma\Users\Address');
        $this->contactInfo->address[1]->shouldBeLike((object) [
            'line1' => '123 Something Blvd.',
            'city' => 'Somewhere',
            'state_province' => 'IL',
            'postal_code' => '12345',
            'country' => (object) ['value' => 'USA'],
            'address_type' => [(object) ['value' => 'home']]
        ]);
    }

    public function it_can_remove_address()
    {
        $address = $this->contactInfo->addresses[0];
        $this->contactInfo->removeAddress($address);
        $this->contactInfo->addresses->shouldBe([]);
    }

    public function it_can_set_preferred_address()
    {
        $address = $this->contactInfo->addresses[0];
        $address->preferred = true;
        $address->preferred->shouldBe(true);
        $address->preferred = false;
        $address->preferred->shouldBe(false);
    }

    public function it_can_unset_preferred_address()
    {
        $this->contactInfo->unsetPreferredAddress();
        $this->contactInfo->getPreferredAddress()->shouldBe(null);
    }

    public function it_can_set_address_type()
    {
        $address = $this->contactInfo->addresses[0];
        $address->setAddressType('work', 'Work');
        $address->data->address_type[0]->shouldBeLike((object)[
            'value' => 'work',
            'desc' => 'Work'
        ]);
        $address->address_type = 'home';
        $address->data->address_type[0]->shouldBeLike((object)[
            'value' => 'home'
        ]);
    }

    public function it_can_add_phone_number()
    {
        $this->contactInfo->addPhone('8675309', 'home', true);
        $this->contactInfo->getPreferredPhone()->phone_number->shouldBe('8675309');
    }

    public function it_can_remove_phone_number()
    {
        $this->contactInfo->removePhone('12345678');
        $this->contactInfo->getPreferredPhone()->shouldBe(null);
    }

    public function it_can_set_preferred_phone()
    {
        $this->contactInfo->setPreferredPhone('87654321');
        $this->contactInfo->getPreferredPhone()->phone_number->shouldBe('87654321');
    }

    public function it_can_unset_preferred_phone()
    {
        $this->contactInfo->unsetPreferredPhone();
        $this->contactInfo->getPreferredPhone()->shouldBe(null);
    }

    public function it_can_get_all_phone_numbers()
    {
        $phones = $this->contactInfo->allPhones();
        $phones[0]->phone_number->shouldBe('12345678');
        $phones[1]->phone_number->shouldBe('87654321');
    }
}
