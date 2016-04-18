<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class JobGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\JobGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\JobGenerator');
    }

    function it_supports_jobs()
    {
        $this->supports('jobs')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_jobs(
        ProgressBar $progress
    ) {
        $globalConfig = ['output_dir' => '/tmp/'];
        $entitiesConfig = [];
        $options = [];

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
