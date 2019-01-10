<?php

namespace spec\Scriptotek\Alma\Bibs;

use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Representation;
use Scriptotek\Alma\Bibs\File;
use Scriptotek\Alma\Client as AlmaClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileSpec extends ObjectBehavior
{
    public function let(AlmaClient $client, Bib $bib, Representation $representation)
    {
        $bib->mms_id = '990006312214702204';
        $representation->representation_id = '22163771200002204';
        $file_id = '23163771190002204';

        $this->beConstructedWith($client, $bib, $representation, $file_id);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(File::class);
    }

}
