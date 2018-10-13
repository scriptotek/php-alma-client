<?php

namespace spec\Scriptotek\Alma\TaskLists;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Bibs;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\TaskLists\RequestedResource;
use spec\Scriptotek\Alma\SpecHelper;

class RequestedResourcesSpec extends ObjectBehavior
{
    public function it_provides_filtering_options(AlmaClient $client, Library $library, Bibs $bibs, Bib $bib)
    {
        $library->code = 'SOME_LIBRARY';
        $this->beConstructedWith($client, $library, 'DEFAULT_CIRC_DESK', [
            'printed' => 'N',
        ]);

        $client->bibs = $bibs;
        $bibs->get('991120800814702204')
            ->shouldBeCalled()
            ->willReturn($bib);

        $client->getJSON('/task-lists/requested-resources?printed=N&library=SOME_LIBRARY&circ_desk=DEFAULT_CIRC_DESK&offset=0&limit=10')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('requested-resources_response.json'));

        $result = $this->all();

        $result->shouldBeArray();
        $result->shouldHaveCount(1);
        $result[0]->shouldBeAnInstanceOf(RequestedResource::class);
    }
}
