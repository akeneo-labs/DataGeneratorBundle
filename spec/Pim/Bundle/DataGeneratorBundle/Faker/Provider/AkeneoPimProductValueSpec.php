<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Faker\Provider;

use Faker\Generator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;

class AkeneoPimProductValueSpec extends ObjectBehavior
{
    function let(Generator $generator)
    {
        $this->beConstructedWith($generator);
    }

    function it_generates_product_value(AbstractAttribute $attribute)
    {
        $this->akeneoPimProductValue($attribute)
            ->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductValue');
    }

    function it_generates_product_scopable_value(AbstractAttribute $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $value = $this->akeneoPimProductValue($attribute, 'ecommerce');
        $value->getScope()->shouldReturn('ecommerce');
    }

    function it_generates_product_localizable_value(AbstractAttribute $attribute)
    {
        $attribute->isLocalizable()->willReturn(true);
        $value = $this->akeneoPimProductValue($attribute, null, 'en_US');
        $value->getLocale()->shouldReturn('en_US');
    }

    function it_should_throw_an_exception_if_no_locale_with_localizable_attribute(AbstractAttribute $attribute)
    {
        $attribute->isLocalizable()->willReturn(true);
        $this->shouldThrow('\LogicException')->during('akeneoPimProductValue', $attribute);
    }

    function it_should_throw_an_exception_if_no_channel_with_scopable_attribute(AbstractAttribute $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $this->shouldThrow('\LogicException')->during('akeneoPimProductValue', $attribute);
    }

    function it_generates_product_value_for_varchar(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeString();
    }

    function it_generates_product_value_for_text(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_TEXT);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeString();
    }

    function it_generates_product_value_for_boolean(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_BOOLEAN);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeBoolean();
    }

    function it_generates_product_value_for_date(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DATE);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeInstanceOf('\DateTime');
        $value->getData()->format('H:m:s')->shouldReturn('00:00:00');
    }

    function it_generates_product_value_for_datetime(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DATE);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeInstanceOf('\DateTime');
    }

    function it_generates_product_value_for_decimal(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DECIMAL);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeDecimal();
    }

    function it_generates_product_value_for_integer(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_INTEGER);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeInteger();
    }

    function it_generates_product_value_for_options(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_OPTIONS);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeInArray();
    }

    function it_generates_product_value_for_option(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_OPTION);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeInstanceOf(AttributeOption);
    }

    function it_generates_product_value_for_media(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_MEDIA);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeInstanceOf('Pim\Bundle\Catalog\Model\MediaInterface');
    }

    function it_generates_product_value_for_metric(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_METRIC);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeInstanceOf('Pim\Bundle\Catalog\Model\MetricInterface');
    }

    function it_generates_product_value_for_prices(AbstractAttribute $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_PRICE);

        $value = $this->akeneoPimProductValue($attribute);
        $value->getAttribute()->shouldReturn($attribute);
        $value->getData()->shouldBeArray();
    }
}
