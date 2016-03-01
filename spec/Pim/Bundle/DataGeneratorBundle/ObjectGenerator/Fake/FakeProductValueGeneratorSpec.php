<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData\DataGeneratorInterface;
use Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData\DataGeneratorRegistry;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class FakeProductValueGeneratorSpec extends ObjectBehavior
{
    function let(
        DataGeneratorRegistry $generatorRegistry,
        ProductBuilderInterface $productBuilder
    ) {
        $this->beConstructedWith($generatorRegistry, $productBuilder);
    }

    function it_should_fail_when_generating_localizable_product_value_with_non_localizable_attribute(
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $attribute->isLocalizable(false);
        $attribute->isScopable(false);
        $this->shouldThrow('\LogicException')->duringGenerateProductValue($attribute, $locale);
    }

    function it_should_fail_when_generating_scopable_product_value_with_non_scopable_attribute(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attribute->isLocalizable(false);
        $attribute->isScopable(false);
        $this->shouldThrow('\LogicException')->duringGenerateProductValue($attribute, null, $channel);
    }

    function it_should_fail_when_generating_data_without_data_generator_supporting_it(
        $generatorRegistry,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('my_attribute');
        $generatorRegistry->getDataGenerator($attribute)->willReturn(null);

        $this->shouldThrow('\LogicException')->duringGenerateProductValue($attribute);
    }

    function it_generates_product_value_with_generated_data(
        $generatorRegistry,
        $productBuilder,
        DataGeneratorInterface $dataGenerator,
        AttributeInterface $attribute,
        ProductValueInterface $productValue
    ) {
        $attribute->getBackendType()->willReturn('varchar');
        $generatorRegistry->getDataGenerator($attribute)->willReturn($dataGenerator);
        $dataGenerator->generateData($attribute)->willReturn('my random string');

        $productBuilder->createProductValue($attribute, null, null)->willReturn($productValue);

        $productValue->setData('my random string')->shouldBeCalled();

        $generatedValue = $this->generateProductValue($attribute)->shouldReturn($productValue);
    }
}
