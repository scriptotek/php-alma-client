<?php

namespace spec\Scriptotek\Alma;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Factory;

class BibsSpec extends ObjectBehavior
{

    public function let(AlmaClient $almaClient)
    {
        $this->beConstructedWith($almaClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\Bibs');
    }

    public function it_provides_an_array_interface_to_bib_objects()
    {
        $mms_id = str_random();
        $bib = $this[$mms_id];

        $this->shouldImplement('ArrayAccess');
        $bib->shouldHaveType('Scriptotek\Alma\Models\Bib');
        $bib->mms_id->shouldBe($mms_id);
    }

}
