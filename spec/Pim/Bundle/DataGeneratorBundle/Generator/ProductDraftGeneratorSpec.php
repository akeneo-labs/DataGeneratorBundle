<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\ProductRawBuilder;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class ProductDraftGeneratorSpec extends ObjectBehavior
{
    function let(
        ProductRawBuilder $rawBuilder,
        FamilyRepositoryInterface $familyRepo
    ) {
        $this->beConstructedWith($rawBuilder, $familyRepo);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\ProductDraftGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_generates_products(
        $rawBuilder,
        $familyRepo,
        ProgressBar $progress,
        FamilyInterface $family
    ) {
        $globalConfig = ['output_dir' => '/tmp/', 'seed' => 123456789];
        $entitiesConfig = [
            'count'                                => 1,
            'filled_attributes_count'              => 1,
            'filled_attributes_standard_deviation' => 0,
            'start_index'                          => 0,
            'mandatory_attributes'                 => [],
            'force_values'                         => [],
            'delimiter'                            => ';',
            'filename'                             => 'product.csv',
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
}
