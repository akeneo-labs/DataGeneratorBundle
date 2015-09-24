<?php

namespace Pim\Bundle\DataGeneratorBundle\Faker\Provider;

use Symfony\Component\Locale\Locale as SfLocale;
use Symfony\Component\Intl\Intl;
use Faker\Provider\Base;
use Faker\Factory;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Currency;

/**
 * Generate fake Akeneo base objects (locale, channel, currency)
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseObject extends Base
{
    const CHANNEL_DEFAULT_CURRENCIES_COUNT = 2;
    const CHANNEL_DEFAULT_LOCALES_COUNT = 3;

    /**
     * Generate a fake activated locale
     *
     * @param string $code
     *
     * @return Locale
     */
    public function locale($code = null)
    {
        $locale = new Locale();

        if (null !== $code) {
            $locale->setCode($code);
        } else {
            $localeCodes = SfLocale::getLocales();
            $locale->setCode($this->generator->unique()->randomElement($localeCodes));
        }

        return $locale;
    }

    /**
     * Generate several activated locales
     *
     * @param int   $count
     * @param array $codes
     *
     * @return array
     */
    public function locales($count, array $codes = null)
    {
        $locales = [];

        if (null !== $codes) {
            foreach ($codes as $code) {
                $locales[] = $this->locale($code);
            }
            $count -= count($codes);
        }

        for ($i = 0; $i < $count; $i++) {
            $locale = $this->locale();
            if (null !== $codes) {
                while (in_array($locale->getCode(), $codes)) {
                    $locale = $this->locale();
                }
            }
            $locales[] = $this->locale();
        }

        return $locales;
    }

    /**
     * Generate several currencies
     *
     * @param int   $count
     * @param array $codes
     *
     * @return array
     */
    public function currencies($count, array $codes = null)
    {
        $currencies = [];

        if (null !== $codes) {
            foreach ($codes as $code) {
                $currencies[] = $this->currency($code);
            }
            $count -= count($codes);
        }

        for ($i = 0; $i < $count; $i++) {
            $currency = $this->currency();
            if (null !== $codes) {
                while (in_array($currency->getCode(), $codes)) {
                    $currency = $this->currency();
                }
            }
            $currencies[] = $this->currency();
        }

        return $currencies;
    }

    /**
     * Generate a fake currency
     *
     * @param string $code
     *
     * @return Currency
     */
    public function currency($code = null)
    {
        $currency = new Currency();

        if (null !== $code) {
            $currency->setCode($code);
        } else {
            $currencyCodes = array_keys(Intl::getCurrencyBundle()->getCurrencyNames());
            $currency->setCode($this->generator->unique()->randomElement($currencyCodes));
        }

        return $currency;
    }

    /**
     * Generate a fake Channel
     *
     * @param Faker  $faker
     * @param string $code
     * @param array  $locales
     * @param array  $currencies
     *
     * @return Channel
     */
    public function channel($faker, $code = null, array $locales = null, array $currencies = null)
    {
        $channel = new Channel();

        if (null === $code) {
            strtolower($faker->unique()->word());
        }

        if (null === $locales) {
            $locales = $this->locales(static::CHANNEL_DEFAULT_LOCALES_COUNT);
        }

        if (null === $currencies) {
            $currencies = $this->currencies(static::CHANNEL_DEFAULT_CURRENCIES_COUNT);
        }

        $channel->setCode($code);
        $channel->setLabel(ucfirst($code));
        foreach ($currencies as $currency) {
            $channel->addCurrency($currency);
        }
        foreach ($locales as $locale) {
            $channel->addLocale($locale);
        }

        $channel->setColor($faker->unique()->safeColorName());

        return $channel;
    }

    /**
     * Generate several channels
     *
     * @param int    $count
     * @param array  $codes
     * @param array  $locales
     * @param array  $currencies
     *
     * @return array
     */
    public function channels($count, array $codes = null, array $locales = null, array $currencies = null)
    {
        $faker = Factory::create();

        $channels = [];

        if (null !== $codes) {
            foreach ($codes as $code) {
                $channels[] = $this->channel($faker, $code, $locales, $currencies);
            }

            $count -= count($codes);
        }

        for ($i = 0; $i < $count; $i++) {
            $channels[] = $this->channel($faker, null, $locales, $currencies);
        }

        return $channels;
    }
}
