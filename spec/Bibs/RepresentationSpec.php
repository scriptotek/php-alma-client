<?php

namespace spec\Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\File;
use Scriptotek\Alma\Bibs\Representations;
use Scriptotek\Alma\Bibs\Representation;
use Scriptotek\Alma\Client as AlmaClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\Scriptotek\Alma\SpecHelper;

class RepresentationSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $representation_id = '123';
        $this->beConstructedWith($client, $bib, $representation_id);
    }

    protected function expectRequest($client)
    {
        $client->getJSON('/bibs/abc/representations/123')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('representation_response.json'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Representation::class);
    }

    public function it_fetches_data_when_needed(AlmaClient $client)
    {
        $this->expectRequest($client);

        $this->label->shouldBe('High resolution TIFF images');
    }

    public function it_has_files(AlmaClient $client)
    {
        $client->getJSON('/bibs/abc/representations/123/files')
            ->shouldBeCalled()
            ->willReturn(SpecHelper::getDummyData('files_response.json'));

        $files = $this->files;
        $files->shouldHaveCount(96);

        $files->rewind();
        $files->valid()->shouldBe(true);
        $files->current()->shouldHaveType(File::class);
    }

}
