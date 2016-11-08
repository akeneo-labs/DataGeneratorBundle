<?php

namespace spec\Pim\Bundle\DataGeneratorBundle;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class AttributeKeyProviderSpec extends ObjectBehavior
{
    function let(
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

        $this->beConstructedWith($channelRepository, $localeRepository, $currencyRepository);
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
            'attr-en_US',
            'attr-fr_FR',
            'attr-de_DE',
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
            'attr-en_US-ecommerce',
            'attr-fr_FR-ecommerce',
            'attr-de_DE-ecommerce',
            'attr-en_US-print',
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
            'attr-print',
            'attr-ecommerce-unit',
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
            'attr-en_US-eur',
            'attr-en_US-usd',
            'attr-fr_FR-eur',
            'attr-fr_FR-usd',
            'attr-de_DE-eur',
            'attr-de_DE-usd',
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
