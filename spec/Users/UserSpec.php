<?php

namespace spec\Scriptotek\Alma\Users;

use PhpSpec\ObjectBehavior;
use Psr\Http\Message\UriInterface;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Loans;
use Scriptotek\Alma\Users\User;
use spec\Scriptotek\Alma\SpecHelper;

class UserSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, UriInterface $url)
    {
        $this->beConstructedWith($client, '12345');

        $client->buildUrl('/users/12345', [])
            ->willReturn($url);

        $client->getJSON($url)
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

    function it_has_loans(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);
        $this->loans->shouldHaveType(Loans::class);
    }
}
