<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Generator\AttributeGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\AttributeGroupGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\AttributeOptionGenerator;
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
        CategoryGenerator $categoryGenerator,
        AttributeGroupGenerator $attrGroupGenerator,
        AttributeOptionGenerator $attributeOptionGenerator
    ) {
        $this->beConstructedWith(
            $attributeGenerator,
            $familyGenerator,
            $productGenerator,
            $categoryGenerator,
            $attrGroupGenerator,
            $attributeOptionGenerator
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\Generator');
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }
}
