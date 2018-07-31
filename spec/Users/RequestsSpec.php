<?php

namespace spec\Scriptotek\Alma\Users;

use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Users\Requests;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\Scriptotek\Alma\SpecHelper;

class RequestsSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $url = '/bibs/1/holdings/2/items/3/requests';
        $this->beConstructedWith($client, $url);
    }

    public function it_is_countable(AlmaClient $client)
    {
        $client->getJSON('/bibs/1/holdings/2/items/3/requests')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('item_requests_response.json'));

        $this->shouldHaveCount(1);
    }


    function it_is_initializable()
    {
        $this->shouldHaveType(Requests::class);
    }
}
