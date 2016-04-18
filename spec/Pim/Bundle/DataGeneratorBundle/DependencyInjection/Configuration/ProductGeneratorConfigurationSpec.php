<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\DependencyInjection\Configuration;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductGeneratorConfigurationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\DependencyInjection\Configuration\ProductGeneratorConfiguration');
    }

    function it_is_a_configuration()
    {
        $this->shouldImplement('Symfony\Component\Config\Definition\ConfigurationInterface');
    }

    function it_has_a_configuration_tree_builder()
    {
        $this->getConfigTreeBuilder()->shouldHaveType('Symfony\Component\Config\Definition\Builder\TreeBuilder');
    }
}
