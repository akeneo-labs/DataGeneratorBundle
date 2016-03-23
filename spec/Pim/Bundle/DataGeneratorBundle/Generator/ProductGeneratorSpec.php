<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\ProductRawBuilder;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Prophecy\Argument;

class ProductGeneratorSpec extends ObjectBehavior
{
    function let(
        ProductRawBuilder $builder,
        FamilyRepositoryInterface $familyRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith(
            $builder,
            $familyRepository,
            $groupRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\ProductGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }
}
