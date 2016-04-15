<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\AttributeTypeRegistry;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class AttributeGeneratorSpec extends ObjectBehavior
{
    function let(
        CsvWriter $writer,
        AttributeTypeRegistry $typeRegistry
    ) {
        $this->beConstructedWith($writer, $typeRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\AttributeGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_attributes()
    {
        $this->supports('attributes')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_attributes(
        $writer,
        $typeRegistry,
        ProgressBar $progress,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        AttributeGroupInterface $attributeGroup1,
        AttributeGroupInterface $attributeGroup2
    ) {
        $globalConfig = ['output_dir' => '/', 'seed' => 123456789];
        $entitiesConfig = [
            'localizable_probability'              => 0,
            'scopable_probability'                 => 0,
            'localizable_and_scopable_probability' => 0,
            'useable_as_grid_filter_probability'   => 0,
            'min_variant_axes'                     => 0,
            'min_variant_attributes'               => 0,
            'identifier_attribute'                 => 'sku',
            'force_attributes'                     => [],
            'count'                                => 1,
        ];
        $options = [
            'locales'          => [$locale1, $locale2],
            'attribute_groups' => [
                'attributeGroup1' => $attributeGroup1,
                'attributeGroup2' => $attributeGroup2,
            ],
        ];

        $locale1->getCode()->willReturn('fr_FR');
        $locale2->getCode()->willReturn('en_US');
        $typeRegistry->getAliases()->willReturn(['pim_text']);

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write([
            'sku' => [
                'code'                   => 'sku',
                'type'                   => 'pim_catalog_identifier',
                'group'                  => 'attributeGroup1',
                'useable_as_grid_filter' => 1,
                'label-fr_FR'            => 'identifier veniam dolores',
                'label-en_US'            => 'identifier placeat aut'
            ],
            'attr_0' => [
                'code'                   => 'attr_0',
                'type'                   => 'pim_text',
                'group'                  => 'attributeGroup2',
                'localizable'            => 0,
                'scopable'               => 0,
                'label-fr_FR'            => 'pim_text et recusandae',
                'label-en_US'            => 'pim_text fugit dolores',
                'useable_as_grid_filter' => 0
            ]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
