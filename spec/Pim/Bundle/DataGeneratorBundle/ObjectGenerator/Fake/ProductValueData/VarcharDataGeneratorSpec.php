<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;


class VarcharDataGeneratorSpec extends ObjectBehavior
{
    function it_is_a_product_value_data_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData\DataGeneratorInterface');
    }

    function it_supports_varchar_backend(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('varchar');
        $this->supportsGeneration($attribute)->shouldReturn(true);
    }

    function it_does_not_support_date_backend(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('date');
        $this->supportsGeneration($attribute)->shouldReturn(false);
    }

    function it_generates_varchar_data(
        AttributeInterface $attribute
    ) {
        $data = $this->generateData($attribute);

        $data->shouldBeString();
        $data->shouldNotBe("");
    }

    function it_generates_url_data_when_attributes_has_url_validation_rule(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('varchar');
        $attribute->getValidationRule()->willReturn('url');

        $this->generateData($attribute)->shouldMatch('#^https?://[a-z0-9\.]+/.*#');
    }
}
