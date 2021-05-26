<?php

namespace spec\Scriptotek\Alma\CodeTables;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Conf\CodeTable;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class CodeTablesSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $this->beConstructedWith($client);
    }

    public function it_provides_a_lazy_interface_to_codetable_objects(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);

        $ctid = 'myCodeTable'; // str_random();
        $bib = $this->get($ctid);

        $bib->shouldHaveType(CodeTable::class);
        $bib->code->shouldBe($ctid);
    }

    public function it_provides_a_lazy_array_interface_to_codetable_objects(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);

        $ctid = 'myCodeTable'; // str_random();
        $ct = $this[$ctid];

        $ct->shouldHaveType(CodeTable::class);
        $ct->code->shouldBe($ctid);
    }

}
