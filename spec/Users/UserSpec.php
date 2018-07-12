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
        $this->beConstructedWith($almaClient, '12345');

        $almaClient->getJSON(Argument::containingString('12345'), Argument::any())
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
}
