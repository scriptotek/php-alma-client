<?php

namespace spec\Scriptotek\Alma\Analytics;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use Scriptotek\Alma\Analytics\Row;
use PhpSpec\ObjectBehavior;

class RowSpec extends ObjectBehavior
{
    public function let()
    {
        $xml = QuiteSimpleXMLElement::make('<Row xmlns="urn:schemas-microsoft-com:xml-analysis:rowset">
                <Column0>0</Column0>
                <Column1>col1 content</Column1>
                <Column2>col2 content</Column2>
            </Row>');
        $xml->registerXPathNamespace('rowset', 'urn:schemas-microsoft-com:xml-analysis:rowset');
        $headers = ['mms_id', 'title'];
        $this->beConstructedWith($xml, $headers);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Row::class);
    }

    public function it_should_have_columns_accessible_by_name()
    {
        $this->mms_id->shouldBe('col1 content');
        $this->title->shouldBe('col2 content');
    }

    public function it_should_have_columns_accessible_by_index()
    {
        $this[0]->shouldBe('col1 content');
        $this[1]->shouldBe('col2 content');
    }

    public function it_should_be_traversable()
    {
        $this->shouldBeAnInstanceOf('Traversable');
    }

    public function it_should_be_countable()
    {
        $this->shouldHaveCount(2);
    }
}
