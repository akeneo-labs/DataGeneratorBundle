<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class ProductGeneratorSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $attributeRepository,
            $channelRepository,
            $localeRepository,
            $currencyRepository,
            $categoryRepository,
            $groupRepository
        );
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_generates_a_product()
    {
        $this->generate()->shouldImplement('Pim\Component\Catalog\Model\ProductInterface');
    }

    function it_generates_a_product_with_an_identifier()
    {
        $this->generate()->getIdentifier()->shouldReturn('sku-001');
    }

    function it_generates_a_product_with_15_product_values()
    {
        $config = [
            "filled_attributes_count"              => 15,
            "filled_attributes_standard_deviation" => 0
        ];

        $this->generate($config)->getValues()->getCount()->shouldReturn(15);
    }

    function it_does_not_generates_a_product_with_15_product_values_when_the_family_has_10_attributes()
    {
        $config = [
            "filled_attributes_count"              => 15,
            "filled_attributes_standard_deviation" => 0
        ];

        $this->generate($config)->getValues()->shouldHaveCount(15);
    }

    function it_generates_a_product_with_10_attributes_when_15_requested_when_family_has_10()
    {
        $config = [
            "filled_attributes_count"              => 15,
            "filled_attributes_standard_deviation" => 0
        ];

        $this->generate($config)->getValues()->getCount()->shouldReturn(10);
    }
}
