<?php

namespace spec\Scriptotek\Alma\TaskLists;

use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\TaskLists\LendingRequests;
use Scriptotek\Alma\TaskLists\LendingRequest;
use PhpSpec\ObjectBehavior;
use spec\Scriptotek\Alma\SpecHelper;

class ResourceSharingRequestSpec extends ObjectBehavior
{
    public function let(AlmaClient $client)
    {
        $id = '123';
        $this->beConstructedWith($client, $id);
    }
}
