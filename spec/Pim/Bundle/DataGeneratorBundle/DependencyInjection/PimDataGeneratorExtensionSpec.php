<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimDataGeneratorExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\DependencyInjection\PimDataGeneratorExtension');
    }

    function it_is_a_configuration()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\DependencyInjection\Extension');
    }

    function it_load_configuration(ContainerBuilder $container)
    {
        $this->load([], $container);
    }
}
