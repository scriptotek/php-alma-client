<?php

namespace spec\Scriptotek\Alma\Bibs;

use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Bibs\Holding;
use Scriptotek\Alma\Bibs\Holdings;
use Scriptotek\Alma\Client as AlmaClient;
use spec\Scriptotek\Alma\SpecHelper;

class HoldingsSpec extends ObjectBehavior
{
    public $sample = '{
          "holding": [
            {
              "library": {
                "value": "65144020000121",
                "desc": "BURNS"
              },
              "location": {
                "value": "UNASSIGNED",
                "desc": "UNASSIGNED location"
              },
              "link": "/almaws/v1/bibs/99100383900121/holdings/2221159990000121",
              "holding_id": "2221159990000121"
            },
            {
              "library": {
                "value": "5867020000121",
                "desc": "Main Library"
              },
              "location": {
                "value": "MICR",
                "desc": "Microforms"
              },
              "link": "/almaws/v1/bibs/99100383900121/holdings/2221410000000121",
              "holding_id": "2221410000000121"
            }
          ],
          "bib_data": {
            "title": "Data base",
            "issn": "0095-0033",
            "publisher": "Association for Computing Machinery",
            "link": "/almaws/v1/bibs/99100383900121",
            "mms_id": 99100383900121,
            "place_of_publication": "New York :",
            "network_number": [
              "(CONSER)  2011250895",
              "(CKB)954926959913",
              "(OCoLC)604911177"
            ]
          },
          "total_record_count": 2
        }';

    public function let(AlmaClient $client, Bib $bib)
    {
        $bib->mms_id = 'abc';
        $this->beConstructedWith($client, $bib);
    }

    public function it_provides_a_lazy_interface_to_holding_objects(AlmaClient $client, Bib $bib)
    {
        SpecHelper::expectNoRequests($client);

        $holding_id = '12345'; // str_random();
        $holding = $this->get($holding_id);

        $holding->shouldHaveType(Holding::class);
        $holding->bib->shouldBe($bib);
        $holding->holding_id->shouldBe($holding_id);
    }

    public function it_provides_a_lazy_array_interface_to_holding_objects(AlmaClient $client, Bib $bib)
    {
        SpecHelper::expectNoRequests($client);

        $holding_id = '90123'; // str_random();
        $holding = $this[$holding_id];

        $holding->shouldHaveType(Holding::class);
        $holding->bib->shouldBe($bib);
        $holding->holding_id->shouldBe($holding_id);
    }

    public function it_is_countable(AlmaClient $client)
    {
        $client->getJSON('/bibs/abc/holdings')
            ->shouldBeCalled()
            ->willReturn(json_decode($this->sample));

        $this->shouldHaveCount(2);
    }

    public function it_provides_an_iterator_interface_to_holding_objects(AlmaClient $client)
    {
        $client->getJSON('/bibs/abc/holdings')
            ->shouldBeCalled()
            ->willReturn(json_decode($this->sample));

        $this->shouldImplement('Iterator');

        $this->current()->shouldHaveType(Holding::class);
    }
}
