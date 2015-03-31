<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class AttributeGeneratorSpec extends ObjectBehavior
{
    function let(
        AttributeGroupRepositoryInterface $groupRepository,
        LocaleRepositoryInterface $localeRepository,
        AttributeTypeRegistry $typeRegistry
    ) {
        $this->beConstructedWith($groupRepository, $localeRepository, $typeRegistry);
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
