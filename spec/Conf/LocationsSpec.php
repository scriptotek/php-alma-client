<?php

namespace spec\Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\Conf\Location;
use PhpSpec\ObjectBehavior;
use spec\Scriptotek\Alma\SpecHelper;

class LocationsSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Library $library)
    {
        $library->code = 'LIB_CODE';
        $this->beConstructedWith($client, $library);
    }

    function it_provides_a_lazy_interface_to_location_objects(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);

        $code = 'LOC_CODE';
        $location = $this->get($code);

        $location->shouldBeAnInstanceOf(Location::class);
        $location->code->shouldBe($code);
    }

    function it_provides_locations(AlmaClient $client)
    {
        $client->getJSON('/conf/libraries/LIB_CODE/locations')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('locations_response.json'));

        $this->all()->shouldBeArray();
        $this->all()[0]->shouldBeAnInstanceOf(Location::class);
    }
}
