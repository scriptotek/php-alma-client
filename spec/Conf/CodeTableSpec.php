<?php

namespace spec\Scriptotek\Alma\CodeTable;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Conf\CodeTable;
use Scriptotek\Alma\Conf\CodeTables;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Exception\ResourceNotFound;
use spec\Scriptotek\Alma\SpecHelper;

class CodeTableSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $this->beConstructedWith($client, 'systemJobStatus');
    }

    protected function expectRequest($client)
    {
        $client->getXML('/conf/code-table/systemJobStatus')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('codetable_response.json'));
    }

    public function it_is_lazy(AlmaClient $client)
    {
        SpecHelper::expectNoRequests($client);
        $this->shouldHaveType(CodeTable::class);
    }

    public function it_fetches_record_data_when_needed(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->name->('systemJobStatus');
        $this->subSystem->value->shouldBe('INFRA');
    }

    public function it_can_exist(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->exists()->shouldBe(true);
    }
}
