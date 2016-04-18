<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class AssetCategoryGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\AssetCategoryGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_should_support_asset_category_accesses()
    {
        $this->supports('asset_categories')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_should_generate_an_asset_category(
        $writer,
        ProgressBar $progress,
        LocaleInterface $locale1,
        LocaleInterface $locale2
    ) {
        $globalConfig = ['output_dir' => '/', 'seed' => 123456789];
        $entitiesConfig = [];
        $options = ['locales' => [$locale1, $locale2]];
        $locale1->getCode()->willReturn('fr_FR');
        $locale2->getCode()->willReturn('en_US');

        $writer->setFilename(Argument::any())->willReturn($writer);

        $writer->write([
            [
                'code' => 'asset_main_catalog',
                'parent' => '',
                'label-fr_FR' => 'laudantium aut sed',
                'label-en_US' => 'doloribus molestias minima',
            ]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
