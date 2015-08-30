<?php

namespace spec\Scriptotek\Alma;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Factory;
use Scriptotek\Alma\models\Holding;


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

    public function let(AlmaClient $almaClient)
    {
        $mms_id = 'abc';
        $this->beConstructedWith($mms_id, $almaClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\Holdings');
    }

    public function it_is_countable(AlmaClient $almaClient)
    {
        $almaClient->getJSON(Argument::any())
            ->shouldBeCalled()
            ->willReturn(json_decode($this->sample));

        $this->shouldHaveCount(2);
    }

    public function it_provides_an_iterator_interface_to_holding_objects(AlmaClient $almaClient)
    {
        $almaClient->getJSON(Argument::any())
            ->shouldBeCalled()
            ->willReturn(json_decode($this->sample));

        $this->shouldImplement('Iterator');
        $this->current()->shouldHaveType('Scriptotek\Alma\Models\Holding');
    }

}
