<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class VariantGroupGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\VariantGroupGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_variant_groups()
    {
        $this->supports('variant_groups')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_variant_groups(
        $writer,
        ProgressBar $progress,
        AttributeInterface $axeAttribute,
        AttributeInterface $availableAttribute,
        LocaleInterface $locale,
        GroupTypeInterface $variantGroupType
    ) {
        $globalConfig   = ['output_dir' => '/', 'seed' => 123456789];
        $entitiesConfig = [
            'count'            => 1,
            'axes_count'       => 1,
            'attributes_count' => 1
        ];
        $options = [
            'attributes'  => [$axeAttribute, $availableAttribute],
            'locales'     => [$locale],
            'group_types' => [$variantGroupType],
        ];

        $variantGroupType->getCode()->willReturn('VARIANT');
        $axeAttribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $axeAttribute->isLocalizable()->willReturn(false);
        $axeAttribute->isScopable()->willReturn(false);
        $availableAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $availableAttribute->isLocalizable()->willReturn(false);
        $availableAttribute->isScopable()->willReturn(false);
        $locale->getCode()->willReturn('fr_FR');
        $axeAttribute->getCode()->willReturn('Axe Attribute');
        $availableAttribute->getCode()->willReturn('Available Attribute');

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write([
            [
                'code'                => 'variant_group_0',
                'axis'                => 'Axe Attribute',
                'type'                => 'VARIANT',
                'label-fr_FR'         => 'molestias',
                'Available Attribute' => 'aut sed doloribus'
            ]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
