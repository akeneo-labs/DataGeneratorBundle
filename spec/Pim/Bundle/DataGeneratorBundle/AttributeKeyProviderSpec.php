<?php

namespace spec\Pim\Bundle\DataGeneratorBundle;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class AttributeKeyProviderSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        ChannelInterface $ecommerce,
        ChannelInterface $print,
        LocaleInterface $en,
        LocaleInterface $fr,
        LocaleInterface $de,
        ArrayCollection $ecommerceLocales,
        ArrayCollection $printLocales,
        CurrencyInterface $euro,
        CurrencyInterface $dollar
    ) {
        $currencyRepository->findBy(["activated" => 1])->willReturn([$euro, $dollar]);

        $euro->getCode()->willReturn('eur');
        $dollar->getCode()->willReturn('usd');

        $ecommerceLocales->toArray()->willReturn([$en, $fr, $de]);
        $printLocales->toArray()->willReturn([$en]);

        $ecommerce->getLocales()->willReturn($ecommerceLocales);
        $print->getLocales()->willReturn($printLocales);

        $localeRepository->findBy(["activated" => 1])->willReturn([$en, $fr, $de]);

        $en->getCode()->willReturn('en_US');
        $fr->getCode()->willReturn('fr_FR');
        $de->getCode()->willReturn('de_DE');

        $channelRepository->findAll()->willReturn([$ecommerce, $print]);

        $ecommerce->getCode()->willReturn('ecommerce');
        $print->getCode()->willReturn('print');

        $this->beConstructedWith($attributeRepository, $channelRepository, $localeRepository, $currencyRepository);
    }

    function it_provides_keys_for_simple_attribute(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attr');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');

        $this->getAttributeKeys($attribute)->shouldReturn([
            'attr',
        ]);
    }

    function it_provides_keys_for_scopable_attribute(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attr');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');

        $this->getAttributeKeys($attribute)->shouldReturn([
            'attr-ecommerce',
            'attr-print',
        ]);
    }

    function it_provides_keys_for_localizable_attribute(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attr');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');

        $this->getAttributeKeys($attribute)->shouldReturn([
            'attr-de_DE',
            'attr-en_US',
            'attr-fr_FR',
        ]);
    }

    function it_provides_keys_for_scopable_and_localizable_attribute(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attr');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');

        $this->getAttributeKeys($attribute)->shouldReturn([
            'attr-de_DE-ecommerce',
            'attr-en_US-ecommerce',
            'attr-en_US-print',
            'attr-fr_FR-ecommerce',
        ]);
    }

    function it_provides_keys_for_scopable_metric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attr');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getBackendType()->willReturn('metric');

        $this->getAttributeKeys($attribute)->shouldReturn([
            'attr-ecommerce',
            'attr-ecommerce-unit',
            'attr-print',
            'attr-print-unit',
        ]);
    }

    function it_provides_keys_for_localizable_prices(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attr');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getBackendType()->willReturn('prices');

        $this->getAttributeKeys($attribute)->shouldHaveAttributeKeys([
            'attr-de_DE-eur',
            'attr-de_DE-usd',
            'attr-en_US-eur',
            'attr-en_US-usd',
            'attr-fr_FR-eur',
            'attr-fr_FR-usd',
        ]);
    }

    function it_provides_keys_for_scopable_and_specific_localizable_prices(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attr');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->getLocaleSpecificCodes()->willReturn(['en_US']);

        $attribute->getBackendType()->willReturn('prices');

        $this->getAttributeKeys($attribute)->shouldHaveAttributeKeys([
            'attr-en_US-ecommerce-eur',
            'attr-en_US-ecommerce-usd',
            'attr-en_US-print-eur',
            'attr-en_US-print-usd',
        ]);
    }

    function it_provides_the_keys_of_all_attributes(
        $attributeRepository,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $attributeRepository->findAll()->willReturn([$attribute1, $attribute2]);

        $attribute1->getCode()->willReturn('name');
        $attribute1->isScopable()->willReturn(false);
        $attribute1->isLocalizable()->willReturn(false);
        $attribute1->isLocaleSpecific()->willReturn(false);
        $attribute1->getBackendType()->willReturn('text');

        $attribute2->getCode()->willReturn('description');
        $attribute2->isScopable()->willReturn(true);
        $attribute2->isLocalizable()->willReturn(true);
        $attribute2->isLocaleSpecific()->willReturn(false);
        $attribute2->getBackendType()->willReturn('text');

        $this->getAllAttributesKeys()->shouldReturn([
            'description-de_DE-ecommerce',
            'description-en_US-ecommerce',
            'description-en_US-print',
            'description-fr_FR-ecommerce',
            'name',
        ]);
    }

    public function getMatchers()
    {
        return [
            'haveAttributeKeys' => function ($result, $expected) {
                return count($result) && count($expected) &&
                    [] === array_diff($result, $expected) &&
                    [] === array_diff($expected, $result);
            },
        ];
    }
}
