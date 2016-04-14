<?php

namespace spec\Pim\Bundle\DataGeneratorBundle;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimDataGeneratorBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\PimDataGeneratorBundle');
    }

    function it_is_a_bundle()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\Bundle\Bundle');
    }

    function it_builds(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            Argument::type('Pim\Bundle\DataGeneratorBundle\DependencyInjection\Compiler\RegisterGeneratorsPass')
        )->shouldBeCalled();

        $this->build($container);
    }
}
