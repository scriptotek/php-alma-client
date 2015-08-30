<?php

namespace spec\Scriptotek\Alma\models;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Scriptotek\Alma\Client as AlmaClient;

class BibSpec extends ObjectBehavior
{

    public $sample = '<bib>
          <mms_id>991122800000121</mms_id>
          <holdings link="/almaws/v1/bibs/991122800000121/holdings"/>
          <created_by>exl_impl</created_by>
          <created_date>2013-11-05Z</created_date>
          <last_modified_by>exl_impl</last_modified_by>
          <last_modified_date>2014-01-20Z</last_modified_date>
          <record>
            <leader>00260nam a2200109 u 4500</leader>
            <controlfield tag="001">991122800000121</controlfield>
            <controlfield tag="005">20140120122820.0</controlfield>
            <controlfield tag="008">131105s2013 xx r 000 0 gsw d</controlfield>
            <datafield ind1="1" ind2=" " tag="100">
              <subfield code="a">Smith, John</subfield>
            </datafield>
            <datafield ind1="1" ind2="0" tag="245">
              <subfield code="a">Book of books</subfield>
            </datafield>
          </record>
        </bib>';

    public function let(AlmaClient $almaClient)
    {
        $mms_id = 'abc123';
        $this->beConstructedWith($mms_id, $almaClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Scriptotek\Alma\models\Bib');
    }

    public function it_has_holdings(AlmaClient $almaClient)
        $this->holdings();
    }

    public function it_should_provide_data(AlmaClient $almaClient)
    {
        $xml = new QuiteSimpleXMLElement($this->sample);
        $almaClient->get(Argument::containingString('abc123'))
            ->shouldBeCalled()
            ->willReturn($xml);

        $this->created_date->shouldBe('2013-11-05Z');
    }
}
