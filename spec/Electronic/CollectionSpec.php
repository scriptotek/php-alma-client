<?php

namespace spec\Scriptotek\Alma\Electronic;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Electronic\Collection;
use spec\Scriptotek\Alma\SpecHelper;

class CollectionSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $collection_id = '123';
        $this->beConstructedWith($client, $collection_id);
    }

    protected function expectRequest($client)
    {
        $client->getJSON('/electronic/e-collections/123')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('e-collection_response.json'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Collection::class);
    }
}
