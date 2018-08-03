<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Item;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Bibs\ScanInResponse;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\Users\Loan;
use Scriptotek\Alma\Users\Requests;
use Scriptotek\Alma\Users\User;
use spec\Scriptotek\Alma\SpecHelper;

class ItemSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib, Holding $holding)
    {
        $bib->mms_id = '990006312214702204';
        $holding->holding_id = '22163771200002204';
        $item_id = '23163771190002204';

        $this->beConstructedWith($client, $bib, $holding, $item_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Item::class);
    }

    function it_can_be_checked_out(AlmaClient $client, User $user, Library $library)
    {
        $client->postJSON(
            '/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204/loans?user_id=Dan+Michael',
            [
                'library' => ['value' => 'THAT LIBRARY'],
                'circ_desk' => ['value' => 'DEFAULT_CIRC_DESK']
            ]
        )
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('create_loan_response.json'));

        $user->id = 'Dan Michael';
        $library->code = 'THAT LIBRARY';

        $this->checkOut($user, $library)
            ->shouldHaveType(Loan::class);
    }

    function it_can_be_on_loan(AlmaClient $client, User $user, Library $library)
    {
        $client->getJSON('/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204/loans')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('item_loan_response.json'));

        $this->getLoan()->shouldHaveType(Loan::class);
        $this->loan->shouldHaveType(Loan::class);
    }

    function it_can_be_available(AlmaClient $client, User $user, Library $library)
    {
        $client->getJSON('/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204/loans')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('item_no_loan_response.json'));

        $this->getLoan()->shouldBe(null);
        $this->loan->shouldBe(null);
    }

    function it_can_be_scanned_in(AlmaClient $client, Library $library)
    {
        $client->postJSON('/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204?op=scan&library=THAT+LIBRARY&circ_desk=DEFAULT_CIRC_DESK')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('scanin_transit_response.json'));

        $library->code = 'THAT LIBRARY';

        $this->scanIn($library)
            ->shouldHaveType(ScanInResponse::class);
    }

    function it_can_be_scanned_in_with_params(AlmaClient $client, Library $library)
    {
        $client->postJSON('/bibs/990006312214702204/holdings/22163771200002204/items/23163771190002204?place_on_hold_shelf=true&op=scan&library=THAT+LIBRARY&circ_desk=OTHER_DESK')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('scanin_transit_response.json'));

        $library->code = 'THAT LIBRARY';

        $this->scanIn($library, 'OTHER_DESK', ['place_on_hold_shelf' => 'true'])
            ->shouldHaveType(ScanInResponse::class);
    }

    public function it_has_requests()
    {
        $this->requests->shouldHaveType(Requests::class);
    }
}
