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

        $keys[$attribute->getCode()] = [];

        $updatedKeys = [];
        if ($attribute->isScopable() && $attribute->isLocalizable()) {
            foreach ($this->getLocales() as $locale) {
                foreach ($this->getChannels() as $channel) {
                    foreach ($keys as $baseKey => $keyOptions) {
                        $key               = $baseKey . '-' . $locale->getCode() . '-' . $channel->getCode();
                        $updatedKeys[$key] = array_merge($keyOptions, ['locale' => $locale, 'channel' => $channel]);
                    }
                }
            }
            $keys = $updatedKeys;
        } elseif ($attribute->isScopable() && !$attribute->isLocalizable()) {
            foreach ($this->getChannels() as $channel) {
                foreach ($keys as $baseKey => $keyOptions) {
                    $key               = $baseKey . '-' . $channel->getCode();
                    $updatedKeys[$key] = array_merge($keyOptions, ['channel' => $channel]);
                }
            }
            $keys = $updatedKeys;
        } elseif (!$attribute->isScopable() && $attribute->isLocalizable()) {
            foreach ($this->getLocales() as $locale) {
                foreach ($keys as $baseKey => $keyOptions) {
                    $key               = $baseKey . '-' . $locale->getCode();
                    $updatedKeys[$key] = array_merge($keyOptions, ['locale' => $locale]);
                }
            }
            $keys = $updatedKeys;
        }

        switch ($attribute->getBackendType()) {
            case 'prices':
                $updatedKeys = [];

                foreach ($keys as $key => $keyOptions) {
                    foreach ($this->getCurrencies() as $currency) {
                        $updatedKeys[$key . '-' . $currency->getCode()] = array_merge(
                            $keyOptions,
                            ['currency' => $currency]
                        );
                    }
                }
                $keys = $updatedKeys;
                break;
            case 'metric':
                $updatedKeys = [];

                foreach ($keys as $key => $keyOptions) {
                    $updatedKeys[$key]                           = $keyOptions;
                    $updatedKeys[$key . '-' . self::METRIC_UNIT] = $keyOptions;
                }
                $keys = $updatedKeys;
                break;
        }

        $enabledKeys = [];
        foreach ($keys as $key => $keyOptions) {
            if ($this->isAttributeKeyValid($keyOptions)) {
                $enabledKeys[] = $key;
            }
        }

        return $enabledKeys;
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
     * Returns true if the key is valid, according to channels locale and currency.
     *
     * @param $options
     *
     * @return bool
     */
    private function isAttributeKeyValid(array $options)
    {
        if (isset($options['channel'])) {
            $channel = $options['channel'];

            if (isset($options['locale']) && !$this->hasChannelLocale($channel, $options['locale'])) {
                return false;
            }

            if (isset($options['currency']) && !$this->hasChannelCurrency($channel, $options['currency'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if channel has activated locale.
     *
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return bool
     */
    private function hasChannelLocale(ChannelInterface $channel, LocaleInterface $locale)
    {
        foreach ($channel->getLocaleCodes() as $availableLocale) {
            if ($locale->getCode() === $availableLocale) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if channel has activated currency.
     *
     * @param ChannelInterface  $channel
     * @param CurrencyInterface $currency
     *
     * @return bool
     */
    private function hasChannelCurrency(ChannelInterface $channel, CurrencyInterface $currency)
    {
        foreach ($channel->getCurrencies() as $availableCurrency) {
            if ($currency->getCode() === $availableCurrency->getCode()) {
                return true;
            }
        }

        return false;
    }

}
