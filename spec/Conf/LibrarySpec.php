<?php

namespace spec\Scriptotek\Alma\Conf;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\Conf\Locations;

class LibrarySpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $libraryCode = 'THAT_LIBRARY';
        $this->beConstructedWith($client, $libraryCode);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Library::class);
    }

    public function it_should_have_locations()
    {
        $this->locations->shouldBeAnInstanceOf(Locations::class);
    }
}
