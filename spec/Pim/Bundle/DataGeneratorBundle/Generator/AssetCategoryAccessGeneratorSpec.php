<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class AssetCategoryAccessGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\AssetCategoryAccessGenerator');
    }

    function it_should_be_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_should_support_asset_category_accesses()
    {
        $this->supports('asset_category_accesses')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_should_generate_accesses_for_all_groups_except_all(
        $writer,
        Group $userGroup1,
        Group $userGroup2,
        Group $userGroupAll,
        ProgressBar $progress
    ) {
        $userGroup1->getName()->willReturn('UserGroup1');
        $userGroup2->getName()->willReturn('UserGroup2');
        $userGroupAll->getName()->willReturn('All');

        $globalConfig   = ['output_dir' => '/'];
        $entitiesConfig = [];
        $options        = [
            'user_groups'          => [ $userGroup1, $userGroup2 ],
            'asset_category_codes' => [ 'code1', 'code2' ]
        ];

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write(
            [
                [
                    'category'   => 'code1',
                    'view_items' => 'UserGroup1,UserGroup2',
                    'edit_items' => 'UserGroup1,UserGroup2'
                ], [
                    'category'   => 'code2',
                    'view_items' => 'UserGroup1,UserGroup2',
                    'edit_items' => 'UserGroup1,UserGroup2'
                ]
            ]
        )->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
