<?php

namespace spec\Scriptotek\Alma\models;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;

class HoldingSpec extends ObjectBehavior
{
    public function let(AlmaClient $almaClient)
    {
        $mms_id = 'abc';
        $holdings_id = '123';
        $this->beConstructedWith($almaClient, $mms_id, $holdings_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\models\Holding');
    }
}
