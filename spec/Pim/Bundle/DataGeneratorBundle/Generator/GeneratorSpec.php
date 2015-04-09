<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Generator\AttributeGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\CategoryGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\FamilyGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\ProductGenerator;
use Prophecy\Argument;

class GeneratorSpec extends ObjectBehavior
{
    function let(
        AttributeGenerator $attributeGenerator,
        FamilyGenerator $familyGenerator,
        ProductGenerator $productGenerator,
        CategoryGenerator $categoryGenerator
    ) {
        $this->beConstructedWith($attributeGenerator, $familyGenerator, $productGenerator, $categoryGenerator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\Generator');
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }
}
