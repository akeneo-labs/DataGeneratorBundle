<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class UserGroupGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\UserGroupGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_user_groups()
    {
        $this->supports('user_groups')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_user_groups(
        $writer,
        ProgressBar $progress
    ) {
        $globalConfig = ['output_dir' => '/'];
        $entitiesConfig = [['name' => 'Group']];
        $config = [];

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write([
            ['name' => 'All'],
            ['name' => 'Group']
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $config);
    }
}
