<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake;

use PhpSpec\ObjectBehavior;

class FakeProductValueGeneratorSpec extends ObjectBehavior
{
    function it_generates_product_value()
    {
        $this->generateProductValue()->shouldImplement('Pim\Component\Catalog\Model\ProductInterface');
    }

    function it_generates_non_localizable_and_non_scopable_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $productValue = $this->generateProductValue($attribute);

        $productValue->getLocale()->shouldReturn(null);
        $productValue->getScope()->shouldReturn(null);
    }

    function it_generates_localizable_product_value(
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $locale->getCode()->willReturn("en_US");
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);

        $productValue = $this->generateProductValue($attribute, $locale);

        $productValue->getLocale()->shouldReturn("en_US");
        $productValue->getScope()->shouldReturn(null);
    }

    function it_generates_scopable_product_value(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $channel->getCode()->willReturn("print");
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);

        $productValue = $this->generateProductValue($attribute, null, $channel);

        $productValue->getLocale()->shouldReturn(null);
        $productValue->getScope()->shouldReturn("print");
    }

    function it_should_fail_when_generating_localizable_product_value_with_non_localizable_attribute(
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $attribute->isLocalizable(false);
        $attribute->isScopable(false);
        $this->generateProductValue($attribute, $locale)->shouldThrow('\LogicException');
    }

    function it_should_fail_when_generating_scopable_product_value_with_non_scopable_attribute(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attribute->isLocalizable(false);
        $attribute->isScopable(false);
        $this->generateProductValue($attribute, $channel)->shouldThrow('\LogicException');
    }

    function it_generates_non_empty_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('varchar');
        $productValue = $this->generateProductValue($attribute);

        $productValue->getData()->shouldNotBe(null);
    }

    function it_generates_text_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('text');
        $productValue = $this->generate(null, $attribute);

        $productValue->getText()->shouldBeString();
        $productValue->getText()->shouldNotBeEmpty();
    }

    function it_generates_metric_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('metric');
        $productValue = $this->generate(null, $attribute);

        $productValue->getMetric()->shouldImplement('Pim\Component\Catalog\Model\MetricInterface');
        $productValue->getMetric()->getData()->shouldNotBeEmpty();
    }

    function it_generates_boolean_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('boolean');
        $productValue = $this->generate(null, $attribute);

        $productValue->getBoolean()->shouldBeBoolean();
        $productValue->getBoolean()->shouldNotBeEmpty();
    }

    function it_generates_option_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('option');
        $productValue = $this->generate(null, $attribute);

        $productValue->getOption()->shouldImplement('Pim\Component\Catalog\Model\AttributeOptionInterface');
        $productValue->getOption()->getData()->shouldNotBeEmpty();
    }

    function it_generates_options_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('options');
        $productValue = $this->generate(null, $attribute);

        $productValue->getOptions()->shouldImplement('Doctrine\Common\Collections\Collection');
        $productValue->getOptions()->first()->shouldImplement('Pim\Component\Catalog\Model\AttributeOptionInterface');
    }

    function it_generates_date_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('date');
        $productValue = $this->generate(null, $attribute);

        $productValue->getDate()->shouldBeDateTime();
        $productValue->getDate()->getData()->shouldNotBeEmpty();
    }

    function it_generates_prices_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('prices');
        $productValue = $this->generate(null, $attribute);

        $productValue->getOptions()->shouldImplement('Doctrine\Common\Collections\Collection');
        $productValue->getOptions()->first()->shouldImplement('Pim\Component\Catalog\Model\AttributeOptionInterface');
    }

    function it_generates_media_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('media');
        $productValue = $this->generate(null, $attribute);

        $productValue->getMedia()->shouldImplement('Akeneo\Component\FileStorage\Model\FileInfoInterface');
    }

    function it_generates_url_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('varchar');
        $attribute->getValidationRule()->willReturn('url');

        $productValue = $this->generate(null, $attribute);

        $productValue->getVarchar()->shouldBeUrl();
    }

    function it_generates_decimal_data_with_limits(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('decimal');
        $attribute->getNumberMin()->willReturn('10');
        $attribute->getNumberMax()->willReturn('15');

        $productValue = $this->generate(null, $attribute);

        $productValue->getDecimal()->shouldBeBetween(10, 15);
    }
}
