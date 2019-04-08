<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Files;
use Scriptotek\Alma\Bibs\Representation;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class FilesSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib, Representation $representation)
    {
        $bib->mms_id = 'abc';
        $representation->representation_id = '123';
        $this->beConstructedWith($client, $bib, $representation);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Files::class);
    }

    protected function expectRequest($client)
    {
        $client->getJSON('/bibs/abc/representations/123/files')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('files_response.json'));
    }

    public function it_is_countable(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->shouldHaveCount(96);
    }
}
