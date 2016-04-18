<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class ChannelGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\ChannelGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_channels()
    {
        $this->supports('channels')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_channel(
        $writer,
        ProgressBar $progress
    ) {
        $globalConfig = ['output_dir' => '/'];
        $entitiesConfig = [
            'ecommerce' => [
                'code'       => 'ecommerce',
                'label'      => 'Ecommerce',
                'locales'    => ['fr_FR', 'en_US'],
                'currencies' => ['USD', 'EUR'],
                'color'      => 'blue',
            ]
        ];
        $options = [];

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write([
            [
                'code' => 'USD',
                'activated' => 1
            ], [
                'code' => 'EUR',
                'activated' => 1
            ]
        ])->shouldBeCalled();
        $writer->write([
            [
                'code' => 'ecommerce',
                'label' => 'Ecommerce',
                'tree' => 'master',
                'locales' => 'fr_FR,en_US',
                'currencies' => 'USD,EUR'
            ]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
