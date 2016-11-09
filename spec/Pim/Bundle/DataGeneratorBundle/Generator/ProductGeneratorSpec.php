<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\AttributeKeyProvider;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\ProductRawBuilder;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class ProductGeneratorSpec extends ObjectBehavior
{
    function let(
        ProductRawBuilder $rawBuilder,
        FamilyRepositoryInterface $familyRepo,
        GroupRepositoryInterface $groupRepo,
        AttributeKeyProvider $attributeKeyProvider
    ) {
        $this->beConstructedWith($rawBuilder, $familyRepo, $groupRepo, $attributeKeyProvider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\ProductGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_products()
    {
        $this->supports('products')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_products(
        $rawBuilder,
        $familyRepo,
        ProgressBar $progress,
        FamilyInterface $family
    ) {
        $globalConfig = ['output_dir' => '/tmp/', 'seed' => 123456789];
        $entitiesConfig = [
            'count' => 1,
            'filled_attributes_count' => 1,
            'filled_attributes_standard_deviation' => 0,
            'start_index' => 0,
            'categories_count' => 0,
            'products_per_variant_group' => 0,
            'mandatory_attributes' => [],
            'force_values' => [],
            'percentage_complete' => 0,
            'filename' => 'product_draft.csv',
            'delimiter' => ';',
            'all_attribute_keys' => false
        ];
        $options = [];
        $raw = [
            'sku'    => 'id-0',
            'family' => 'family_code',
            'groups' => '',
        ];

        $familyRepo->findAll()->willReturn([$family]);
        $family->getCode()->willReturn('family_code');
        $rawBuilder->buildBaseProduct($family, 'id-0', '')->willReturn($raw);
        $rawBuilder->setFakerGenerator(Argument::any())->willReturn(null);
        $rawBuilder->fillInRandomCategories($raw, 0)->willReturn(null);
        $rawBuilder->fillInRandomAttributes($family, $raw, [], 1, 0)->willReturn(null);
        $rawBuilder->fillInMandatoryAttributes($family, $raw, [], [])->willReturn(null);

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }

    function it_generates_products_with_all_attribute_keys(
        $rawBuilder,
        $familyRepo,
        $attributeKeyProvider,
        ProgressBar $progress,
        FamilyInterface $family
    ) {
        $globalConfig = ['output_dir' => '/tmp/', 'seed' => 123456789];
        $entitiesConfig = [
            'count' => 1,
            'filled_attributes_count' => 1,
            'filled_attributes_standard_deviation' => 0,
            'start_index' => 0,
            'categories_count' => 0,
            'products_per_variant_group' => 0,
            'mandatory_attributes' => [],
            'force_values' => [],
            'percentage_complete' => 0,
            'filename' => 'product_draft.csv',
            'delimiter' => ';',
            'all_attribute_keys' => true
        ];
        $options = [];
        $raw = [
            'sku'    => 'id-0',
            'family' => 'family_code',
            'groups' => '',
        ];

        $familyRepo->findAll()->willReturn([$family]);
        $family->getCode()->willReturn('family_code');
        $rawBuilder->buildBaseProduct($family, 'id-0', '')->willReturn($raw);
        $rawBuilder->setFakerGenerator(Argument::any())->willReturn(null);
        $rawBuilder->fillInRandomCategories($raw, 0)->willReturn(null);
        $rawBuilder->fillInRandomAttributes($family, $raw, [], 1, 0)->willReturn(null);
        $rawBuilder->fillInMandatoryAttributes($family, $raw, [], [])->willReturn(null);
        $attributeKeyProvider->getAllAttributesKeys()->shouldBeCalled()->willReturn([]);

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
