<?php

namespace spec\Scriptotek\Alma\TaskLists;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\TaskLists\ResourceSharingRequest;
use spec\Scriptotek\Alma\SpecHelper;

class LendingRequestsSpec extends ObjectBehavior
{
    public function it_provides_filtering_options(AlmaClient $client, Library $library)
    {
        $library->code = 'SOME_LIBRARY';
        $this->beConstructedWith($client, $library, [
            'printed' => 'N',
            'status'  => 'REQUEST_CREATED_LEND',
        ]);

        $client->getJSON('/task-lists/rs/lending-requests?printed=N&status=REQUEST_CREATED_LEND&library=SOME_LIBRARY')
            ->shouldBeCalledTimes(1)
            ->willReturn(SpecHelper::getDummyData('lending_requests_created.json'));

        $this->all()->shouldBeArray();
        $this->all()->shouldHaveCount(3);
        $this->all()[0]->shouldBeAnInstanceOf(ResourceSharingRequest::class);
    }
}
