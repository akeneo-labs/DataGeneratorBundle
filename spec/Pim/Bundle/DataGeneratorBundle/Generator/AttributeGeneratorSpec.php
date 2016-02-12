<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Prophecy\Argument;

class AttributeGeneratorSpec extends ObjectBehavior
{
    function let(
        AttributeTypeRegistry $typeRegistry
    ) {
        $this->beConstructedWith($typeRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\AttributeGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }
}
