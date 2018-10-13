<?php

namespace spec\Scriptotek\Alma\Conf;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\Conf\Location;

class LocationSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Library $library)
    {
        $location_code = 'sq10s9pg';
        $this->beConstructedWith($client, $library, $location_code);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Location::class);
    }

    public function it_should_belong_to_a_library()
    {
        $this->library->shouldBeAnInstanceOf(Library::class);
    }
}
