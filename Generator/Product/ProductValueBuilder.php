<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator\Product;

use Faker;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\CurrencyInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

/**
 * Build a raw product value (ie: as an array) with random data.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueBuilder
{
    const METRIC_UNIT = 'unit';
    const DEFAULT_NUMBER_MIN = '0';
    const DEFAULT_NUMBER_MAX = '1000';
    const DEFAULT_NB_DECIMALS = '4';

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;

    /** @var Faker\Generator */
    private $faker;

    /** @var ChannelInterface[] */
    private $channels;

    /** @var CurrencyInterface[] */
    private $currencies;

    /** @var LocaleInterface[] */
    private $locales;

    /** @var AttributeOptionInterface[] */
    private $attributeOptions;

    /**
     * ProductValueBuilder constructor.
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
     * @param Faker\Generator $faker
     *
     * @return ProductValueBuilder
     */
    public function setFakerGenerator(Faker\Generator $faker)
    {
        $this->faker = $faker;

        return $this;
    }

    /**
     * Generate a value in term of one or several entries in the product array
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    public function build(AttributeInterface $attribute)
    {
        if (null === $this->faker) {
            throw new \LogicException('Please set the faker generator before using this method.');
        }

        $valueData = [];
        $keys      = $this->getAttributeKeys($attribute);

        foreach ($keys as $key) {
            $valueData[$key] = $this->generateValueData($attribute, $key);
        }

        return $valueData;
    }

    /**
     * Generate value content based on backend type
     *
     * @param AttributeInterface $attribute
     * @param string             $key
     *
     * @return string
     */
    private function generateValueData(AttributeInterface $attribute, $key)
    {
        if (preg_match('/-' . self::METRIC_UNIT . '$/', $key)) {
            return $attribute->getDefaultMetricUnit();
        }

        switch ($attribute->getBackendType()) {
            case "varchar":
                $data = $this->generateVarcharData($attribute);
                break;
            case "text":
                $data = $this->generateTextData();
                break;
            case "date":
                $data = $this->generateDateData($attribute);
                break;
            case "metric":
            case "decimal":
            case "prices":
                $data = $this->generateNumberData($attribute);
                break;
            case "boolean":
                $data = $this->generateBooleanData();
                break;
            case "option":
            case "options":
                $data = $this->generateOptionData($attribute);
                break;
            default:
                $data = '';
                break;
        }

        return (string)$data;
    }

    /**
     * Provides the potential column keys for this attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    private function getAttributeKeys(AttributeInterface $attribute)
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
     * Generate a varchar product value data
     *
     * @param AttributeInterface $attribute
     *
     * @return string
     */
    private function generateVarcharData(AttributeInterface $attribute)
    {
        $validationRule = $attribute->getValidationRule();
        switch ($validationRule) {
            case 'url':
                $varchar = $this->faker->url();
                break;
            default:
                $varchar = $this->faker->sentence();
                break;
        }

        return $varchar;
    }

    /**
     * Generate a text product value data
     *
     * @return string
     */
    private function generateTextData()
    {
        return $this->faker->sentence();
    }

    /**
     * Generate a date product value data
     *
     * @param AttributeInterface $attribute
     *
     * @return string
     */
    private function generateDateData(AttributeInterface $attribute)
    {
        $date = $this->faker->dateTimeBetween($attribute->getDateMin(), $attribute->getDateMax());

        return $date->format('Y-m-d');
    }

    /**
     * Generate number data
     *
     * @param AttributeInterface $attribute
     *
     * @return string
     */
    private function generateNumberData(AttributeInterface $attribute)
    {
        $min = ($attribute->getNumberMin() != null) ? $attribute->getNumberMin() : self::DEFAULT_NUMBER_MIN;
        $max = ($attribute->getNumberMax() != null) ? $attribute->getNumberMax() : self::DEFAULT_NUMBER_MAX;

        $decimals = $attribute->isDecimalsAllowed() ? self::DEFAULT_NB_DECIMALS : 0;

        $number = $this->faker->randomFloat($decimals, $min, $max);

        return (string)$number;
    }

    /**
     * Generate a boolean product value data
     *
     * @return string
     */
    private function generateBooleanData()
    {
        return $this->faker->boolean() ? "1" : "0";
    }

    /**
     * Generate option data
     *
     * @param AttributeInterface $attribute
     *
     * @return string
     */
    private function generateOptionData(AttributeInterface $attribute)
    {
        $optionCode = "";

        $option = $this->getRandomOptionFromAttribute($attribute);

        if (is_object($option)) {
            $optionCode = $option->getCode();
        }

        return $optionCode;
    }

    /**
     * Get a random option from an attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return AttributeOptionInterface
     */
    private function getRandomOptionFromAttribute(AttributeInterface $attribute)
    {
        if (!isset($this->attributeOptions[$attribute->getCode()])) {
            $this->attributeOptions[$attribute->getCode()] = [];

            foreach ($attribute->getOptions() as $option) {
                $this->attributeOptions[$attribute->getCode()][] = $option;
            }
        }

        return $this->faker->randomElement($this->attributeOptions[$attribute->getCode()]);
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
}
