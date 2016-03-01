<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class FakeProductGeneratorSpec extends ObjectBehavior
{
    function let(
        
    )
    {
        $this->beConstructedWith(
    }

    function it_generates_product()
    {
        $this->generateProduct('sku-001', $family, 10, false)->shouldImplement('Pim\Component\Catalog\Model\ProductInterface');
    }

    function it_generates_a_product_with_an_identifier()
    {
        $this->generateProduct('sku-001',[]);

        $this->generateProduct()->getIdentifier()->shouldReturn('sku-001');
    }
}
