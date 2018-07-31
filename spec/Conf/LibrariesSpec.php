<?php

namespace spec\Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Libraries;
use Scriptotek\Alma\Conf\Library;
use PhpSpec\ObjectBehavior;
use spec\Scriptotek\Alma\SpecHelper;

class LibrariesSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_muh()
    {
        $this->shouldBeAnInstanceOf(Libraries::class);
        $this->shouldImplement(\Countable::class);
        $this->shouldImplement(\Iterator::class);
    }

    function it_provides_a_lazy_interface_to_libary_objects(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);

        $libraryCode = 'THAT_LIBRARY';
        $library = $this->get($libraryCode);

        $library->shouldBeAnInstanceOf(Library::class);
        $library->code->shouldBe($libraryCode);
    }

    function it_provides_libraries(AlmaClient $client)
    {
        $client->getJSON('/conf/libraries')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('libraries_response.json'));

        $this->shouldHaveCount(27);
        $this->all()->shouldBeArray();
        $this->all()[0]->shouldBeAnInstanceOf(Library::class);
    }
}
