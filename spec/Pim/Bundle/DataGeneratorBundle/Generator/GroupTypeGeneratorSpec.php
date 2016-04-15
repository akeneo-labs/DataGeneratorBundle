<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class GroupTypeGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GroupTypeGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_group_types()
    {
        $this->supports('group_types')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_group_types(
        $writer,
        ProgressBar $progress
    ) {
        $globalConfig   = ['output_dir' => '/'];
        $entitiesConfig = [];
        $options        = [];

        $writer->setFilename(Argument::any())->willReturn($writer);

        $writer->write([
            ['code' => 'VARIANT', 'is_variant' => 1],
            ['code' => 'RELATED', 'is_variant' => 0]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
