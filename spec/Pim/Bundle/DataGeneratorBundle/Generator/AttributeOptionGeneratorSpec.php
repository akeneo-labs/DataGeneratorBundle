<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class AttributeOptionGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\AttributeOptionGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_attribute_options()
    {
        $this->supports('attribute_options')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generate_attribute_options(
        $writer,
        ProgressBar $progress,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        AttributeInterface $attributeText,
        AttributeInterface $attributeSelect
    ) {
        $globalConfig = ['output_dir' => '/', 'seed' => 123456789];
        $entitiesConfig = ['count_per_attribute' => 2];
        $options = [
            'locales'    => [$locale1, $locale2],
            'attributes' => [$attributeText, $attributeSelect],
        ];

        $locale1->getCode()->willReturn('fr_FR');
        $locale2->getCode()->willReturn('en_US');

        $attributeText->getAttributeType()->willReturn('pim_catalog_text');
        $attributeSelect->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attributeSelect->getCode()->willReturn('code_select');

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write([
            'attr_opt_code_select0' => [
                'attribute'   => 'code_select',
                'code'        => 'attr_opt_code_select0',
                'sort_order'  => 1,
                'label-fr_FR' => 'Sed doloribus.',
                'label-en_US' => 'Minima veniam dolores.'
            ],
            'attr_opt_code_select1' => [
                'attribute'   => 'code_select',
                'code'        => 'attr_opt_code_select1',
                'sort_order'  => 2,
                'label-fr_FR' => 'Et recusandae fugit.',
                'label-en_US' => 'Sequi beatae.'
            ]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
