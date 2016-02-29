<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\RandomReader;

use PhpSpec\ObjectBehavior;

class ProductValueGenerator extends ObjectBehavior
{
    function it_generates_product_value()
    {
        $this->shouldImplement('Pim\Component\Catalog\Model\ProductInterface');
    }

    function it_generates_localizable_product_value(
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $locale->getCode()->willReturn("en_US");
        $attribute->isLocalizable()->willReturn(true);

        $productValue = $this->generate($attribute, $locale);

        $productValue->getLocale()->shouldReturn("en_US");
    }

    function it_generates_scopable_product_value(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $channel->getCode()->willReturn("print");
        $attribute->isScopable()->willReturn(true);

        $productValue = $this->generate($attribute, null, $channel);

        $productValue->getScope()->shouldReturn("print");
    }

    function it_should_fail_when_generating_localizable_product_value_with_non_localizable_attribute(
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $attribute->isLocalizable(false);
        $this->shouldThrow('\LogicException')->duringGenerate($attribute, $locale);
    }

    function it_should_fail_when_generating_scopable_product_value_with_non_scopable_attribute(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attribute->isScopable(false);
        $this->shouldThrow('\LogicException')->duringGenerate($attribute, null, $channel);
    }

    function it_generates_varchar_product_value(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('varchar');
        $productValue = $this->generate($attribute);

        $productValue->getVarchar()->shouldBeString();
        $productValue->getVarchar()->shouldNotBeEmpty();
    }

    function it_generates_text_product_value()
    {
        $productValue = $this->generate($attribute);

        $productValue->getText()->shouldBeString();
        $productValue->getText()->shouldNotBeEmpty();
    }

    function it_generates_metric_product_value()
    {
        $productValue = $this->generate($attribute);

        $productValue->getMetric()->shouldImplement('Pim\Component\Catalog\Model\MetricInterface');
        $productValue->getMetric()->getData()->shouldNotBeEmpty();
    }

    function it_generates_boolean_product_value()
    {
        $productValue = $this->generate($attribute);

        $productValue->getBoolean()->shouldBeBoolean();
        $productValue->getBoolean()->shouldNotBeEmpty();
    }

    function it_generates_option_product_value()
    {
        $productValue = $this->generate($attribute);

        $productValue->getOption()->shouldImplement('Pim\Component\Catalog\Model\AttributeOptionInterface');
        $productValue->getOption()->getData()->shouldNotBeEmpty();
    }

    function it_generates_options_product_value()
    {
        $productValue = $this->generate($attribute);

        $productValue->getOptions()->shouldImplement('Doctrine\Common\Collections\Collection');
        $productValue->getOptions()->first()->shouldImplement('Pim\Component\Catalog\Model\AttributeOptionInterface');
    }

    function it_generates_date_product_value()
    {
        $productValue = $this->generate($attribute);

        $productValue->getDate()->shouldBeDateTime();
        $productValue->getDate()->getData()->shouldNotBeEmpty();
    }

    function it_generates_prices_product_value()
    {
        $productValue = $this->generate($attribute);

        $productValue->getOptions()->shouldImplement('Doctrine\Common\Collections\Collection');
        $productValue->getOptions()->first()->shouldImplement('Pim\Component\Catalog\Model\AttributeOptionInterface');
    }

    function it_generates_media_product_value()
    {
        $productValue = $this->generate($attribute);

        $productValue->getMedia()->shouldImplement('Akeneo\Component\FileStorage\Model\FileInfoInterface');
    }
}
