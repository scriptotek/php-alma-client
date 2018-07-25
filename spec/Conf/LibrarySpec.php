<?php

namespace spec\Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Library;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Conf\Locations;

class LibrarySpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $libraryCode = 'THAT_LIBRARY';
        $this->beConstructedWith($client, $libraryCode);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Library::class);
    }

    function it_should_have_locations()
    {
        $this->locations->shouldBeAnInstanceOf(Locations::class);
    }
}
