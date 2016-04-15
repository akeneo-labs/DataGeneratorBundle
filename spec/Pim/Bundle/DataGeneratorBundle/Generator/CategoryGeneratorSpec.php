<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class CategoryGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\CategoryGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_categories()
    {
        $this->supports('categories')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_categories(
        $writer,
        ProgressBar $progress,
        LocaleInterface $locale
    ) {
        $globalConfig   = ['output_dir' => '/', 'seed' => 123456789];
        $entitiesConfig = [
            'count'  => 3,
            'levels' => 2,
        ];
        $options = ['locales' => [$locale]];
        $locale->getCode()->willReturn('fr_FR');

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write([
            [
                'code' => 'master',
                'parent' => '',
                'label-fr_FR' => 'Master Catalog',
            ], [
                'code' => 'master_0',
                'parent' => 'master',
                'label-fr_FR' => 'Aut sed.',
            ], [
                'code' => 'master_0_0',
                'parent' => 'master_0',
                'label-fr_FR' => 'Molestias minima veniam.',
            ]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
