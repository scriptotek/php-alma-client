<?php

namespace spec\Scriptotek\Alma\TaskLists;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Client as AlmaClient;

class ResourceSharingRequestSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $id = '123';
        $this->beConstructedWith($client, $id);
    }
}
