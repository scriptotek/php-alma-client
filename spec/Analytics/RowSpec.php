<?php

namespace spec\Scriptotek\Alma\Analytics;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use PhpSpec\ObjectBehavior;
use Scriptotek\Alma\Analytics\Row;

class RowSpec extends ObjectBehavior
{
    public function let()
    {
        // We need to be able to handle missing data, so let's assume we have
        // a report with three columns, but that we for this row only got data
        // for two of the columns (data missing for Column2).
        $xml = QuiteSimpleXMLElement::make('<Row>
                <Column0>0</Column0>
                <Column1>col1 content</Column1>
                <Column3>col3 content</Column3>
            </Row>');
        $xml->registerXPathNamespace('rowset', 'urn:schemas-microsoft-com:xml-analysis:rowset');
        $headers = ['mms_id', 'title', 'isbn'];
        $this->beConstructedWith($xml, $headers);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Row::class);
    }

    public function it_should_have_columns_accessible_by_name()
    {
        $this->mms_id->shouldBe('col1 content');
        $this->title->shouldBe(null);
        $this->isbn->shouldBe('col3 content');
    }

    public function it_should_have_columns_accessible_by_array_key()
    {
        $this['mms_id']->shouldBe('col1 content');
        $this['title']->shouldBe(null);
        $this['isbn']->shouldBe('col3 content');
    }

    public function it_should_have_columns_accessible_by_array_index()
    {
        $this[0]->shouldBe('col1 content');
        $this[1]->shouldBe(null);
        $this[2]->shouldBe('col3 content');
    }

    public function it_should_be_traversable()
    {
        $this->shouldBeAnInstanceOf('Traversable');
    }

    public function it_should_be_countable()
    {
        $this->shouldHaveCount(3);
    }

    public function it_should_be_serializable_as_array()
    {
        $this->toArray()->shouldBeArray();
    }
}
