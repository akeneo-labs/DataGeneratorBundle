<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class FamilyGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\FamilyGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_should_support_families()
    {
        $this->supports('families')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_should_generate_families(
        $writer,
        ProgressBar $progress,
        LocaleInterface $locale,
        AttributeInterface $attribute,
        AttributeInterface $attributeFoo,
        AttributeInterface $attributeMedia,
        ChannelInterface $channel
    ) {
        $globalConfig = ['output_dir' => '/', 'seed' => 123456789];
        $entitiesConfig = [
            'count' => 1,
            'attributes_count'     => 2,
            'requirements_count'   => 2,
            'identifier_attribute' => 'sku',
            'label_attribute'      => 'foo',
        ];
        $options = [
            'locales'    => [$locale],
            'attributes' => [
                'attribute1' => $attribute,
                'foo'        => $attributeFoo,
                'media'      => $attributeMedia,
            ],
            'channels'              => [$channel],
            'media_attribute_codes' => ['media'],
        ];
        $locale->getCode()->willReturn('fr_FR');

        $writer->setFilename(Argument::any())->willReturn($writer);

        $writer->write([
            'fam_0' => [
                'code'               => 'fam_0',
                'label-fr_FR'        => 'Aut sed.',
                'attribute_as_label' => 'foo',
                'attributes'         => 'sku,foo,media',
                'requirements-'      => 'sku,foo'
            ]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
