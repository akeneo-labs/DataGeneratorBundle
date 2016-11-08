<?php

namespace Pim\Bundle\DataGeneratorBundle;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeKeyProvider
{
    const METRIC_UNIT = 'unit';

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;

    /** @var ChannelInterface[] */
    private $channels;

    /** @var CurrencyInterface[] */
    private $currencies;

    /** @var LocaleInterface[] */
    private $locales;

    /**
     * ProductValueRawBuilder constructor.
     *
     * @param ChannelRepositoryInterface  $channelRepository
     * @param LocaleRepositoryInterface   $localeRepository
     * @param CurrencyRepositoryInterface $currencyRepository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Generate the list of attribute keys for a given attribute
     *
     * Example:
     * Attribute: description localizable in english and french for ecommerce
     * Attributes keys are:
     * [ description-en_US-ecommerce, description-fr_FR-ecommerce ]
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    public function getAttributeKeys(AttributeInterface $attribute)
    {
        $keys = [];

        $locales  = $attribute->isLocalizable() ? $this->getLocales() : [null];
        $channels = $attribute->isScopable() ? $this->getChannels() : [null];
        foreach ($channels as $channel) {
            foreach ($locales as $locale) {
                $localeInChannel  = null === $channel ||
                    null === $locale ||
                    in_array($locale, $channel->getLocales()->toArray());
                $localeInSpecific = !$attribute->isLocaleSpecific() ||
                    null === $locale ||
                    in_array($locale->getCode(), $attribute->getLocaleSpecificCodes());

                if ($localeInChannel && $localeInSpecific) {
                    $keys[] = $this->createKey($attribute, $channel, $locale);
                }
            }
        }

        switch ($attribute->getBackendType()) {
            case 'prices':
                foreach ($keys as $index => $key) {
                    foreach ($this->getCurrencies() as $currency) {
                        $keys[] = $key . '-' . $currency->getCode();
                    }
                    unset($keys[$index]);
                }
                break;
            case 'metric':
                foreach ($keys as $index => $key) {
                    $keys[] = $key . '-' .  self::METRIC_UNIT;
                }
                break;
        }

        return $keys;
    }

    /**
     * Get all channels
     *
     * @return ChannelInterface[]
     */
    private function getChannels()
    {
        if (null === $this->channels) {
            $this->channels = [];
            $channels       = $this->channelRepository->findAll();
            foreach ($channels as $channel) {
                $this->channels[$channel->getCode()] = $channel;
            }
        }

        return $this->channels;
    }

    /**
     * Get active currencies
     *
     * @return CurrencyInterface[]
     */
    private function getCurrencies()
    {
        if (null === $this->currencies) {
            $this->currencies = [];
            $currencies       = $this->currencyRepository->findBy(['activated' => 1]);
            foreach ($currencies as $currency) {
                $this->currencies[$currency->getCode()] = $currency;
            }
        }

        return $this->currencies;
    }

    /**
     * Get active locales
     *
     * @return LocaleInterface[]
     */
    private function getLocales()
    {
        if (null === $this->locales) {
            $this->locales = [];
            $locales       = $this->localeRepository->findBy(['activated' => 1]);
            foreach ($locales as $locale) {
                $this->locales[$locale->getCode()] = $locale;
            }
        }

        return $this->locales;
    }

    /**
     * Return the key for the given attribute, locale and channem.
     *
     * @param AttributeInterface    $attribute
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     *
     * @return string
     */
    private function createKey(
        AttributeInterface $attribute,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        $channelCode = null !== $channel ? '-' . $channel->getCode() : '';
        $localeCode = null !== $locale ? '-' . $locale->getCode() : '';

        return $attribute->getCode() . $localeCode . $channelCode;
    }
}
