<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class AttributeGroupAccessGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\AttributeGroupAccessGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_attribute_group_accesses()
    {
        $this->supports('attribute_group_accesses')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_attribute_group_accesses(
        $writer,
        ProgressBar $progress,
        Group $userGroup1,
        Group $userGroup2,
        Group $userGroupAll,
        AttributeGroupInterface $attributeGroup1,
        AttributeGroupInterface $attributeGroup2
    ) {
        $userGroup1->getName()->willReturn('UserGroup1');
        $userGroup2->getName()->willReturn('UserGroup2');
        $userGroupAll->getName()->willReturn('All');
        $attributeGroup1->getCode()->willReturn('code1');
        $attributeGroup2->getCode()->willReturn('code2');

        $globalConfig   = ['output_dir' => '/'];
        $entitiesConfig = [];
        $options        = [
            'user_groups'      => [ $userGroup1, $userGroup2 ],
            'attribute_groups' => [ $attributeGroup1, $attributeGroup2 ]
        ];

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write(
            [
                [
                    'attribute_group' => 'code1',
                    'view_attributes' => 'UserGroup1,UserGroup2',
                    'edit_attributes' => 'UserGroup1,UserGroup2'
                ], [
                    'attribute_group' => 'code2',
                    'view_attributes' => 'UserGroup1,UserGroup2',
                    'edit_attributes' => 'UserGroup1,UserGroup2'
                ]
            ]
        )->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
