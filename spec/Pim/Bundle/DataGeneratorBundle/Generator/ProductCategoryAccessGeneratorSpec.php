<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\CategoryInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class ProductCategoryAccessGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\ProductCategoryAccessGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_product_category_accesses()
    {
        $this->supports('product_category_accesses')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_locale_accesses(
        $writer,
        Group $userGroup1,
        Group $userGroup2,
        Group $userGroupAll,
        CategoryInterface $category1,
        CategoryInterface $category2,
        ProgressBar $progress
    ) {
        $userGroup1->getName()->willReturn('UserGroup1');
        $userGroup2->getName()->willReturn('UserGroup2');
        $userGroupAll->getName()->willReturn('All');
        $category1->getCode()->willReturn('categ1');
        $category2->getCode()->willReturn('categ2');

        $globalConfig   = ['output_dir' => '/'];
        $entitiesConfig = [];
        $options        = [
            'user_groups' => [$userGroup1, $userGroup2],
            'categories'  => [$category1, $category2],
        ];

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write(
            [
                [
                    'category'   => 'categ1',
                    'view_items' => 'UserGroup1,UserGroup2',
                    'edit_items' => 'UserGroup1,UserGroup2',
                    'own_items'  => 'UserGroup1,UserGroup2',
                ], [
                    'category'   => 'categ2',
                    'view_items' => 'UserGroup1,UserGroup2',
                    'edit_items' => 'UserGroup1,UserGroup2',
                    'own_items'  => 'UserGroup1,UserGroup2',
                ]
            ]
        )->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
