<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\DependencyInjection\Compiler;

use Gedmo\Mapping\Annotation\Reference;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterGeneratorsPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\DependencyInjection\Compiler\RegisterGeneratorsPass');
    }

    function it_register_every_generators(ContainerBuilder $container, Definition $registryDefinition)
    {
        $container->getDefinition('pim_data_generator.generator.registry')->willReturn($registryDefinition);
        $container->findTaggedServiceIds('pim_data_generator.generator')->willReturn([
            'generator_id' => 'tags',
            'other_generator_id' => 'tags',
        ]);

        $registryDefinition->addMethodCall('register', Argument::that(function ($params) {
            $check =
                $params[0] instanceof Reference &&
                'generator_id' === $params[0]->__toString()
            ;
            return $check;
        }));

        $registryDefinition->addMethodCall('register', Argument::that(function ($params) {
            $check =
                $params[0] instanceof Reference &&
                'other_generator_id' === $params[0]->__toString()
            ;
            return $check;
        }));

        $this->process($container);
    }
}
