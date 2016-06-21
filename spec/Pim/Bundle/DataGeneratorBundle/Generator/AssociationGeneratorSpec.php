<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class AssociationGeneratorSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith($productRepository, $associationTypeRepository, $objectDetacher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\AssociationGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_generates_associations(
        ProgressBar $progress,
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ObjectDetacherInterface $objectDetacher
    )
    {
        $globalConfig = ['output_dir' => '/tmp/', 'seed' => 123456789];
//        $entitiesConfig = [
//            'count' => 1,
//            'filled_attributes_count' => 1,
//            'filled_attributes_standard_deviation' => 0,
//            'start_index' => 0,
//            'categories_count' => 0,
//            'products_per_variant_group' => 0,
//            'mandatory_attributes' => [],
//            'force_values' => [],
//            'percentage_complete' => 0,
//            'filename' => 'product_draft.csv',
//            'delimiter' => ';',
//        ];
//        $options = [];
//        $raw = [
//            'sku'    => 'id-0',
//            'family' => 'family_code',
//            'groups' => '',
//        ];
//
//        $familyRepo->findAll()->willReturn([$family]);
//        $family->getCode()->willReturn('family_code');
//        $rawBuilder->buildBaseProduct($family, 'id-0', '')->willReturn($raw);
//        $rawBuilder->setFakerGenerator(Argument::any())->willReturn(null);
//        $rawBuilder->fillInRandomCategories($raw, 0)->willReturn(null);
//        $rawBuilder->fillInRandomAttributes($family, $raw, [], 1, 0)->willReturn(null);
//        $rawBuilder->fillInMandatoryAttributes($family, $raw, [], [])->willReturn(null);
//
//        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
