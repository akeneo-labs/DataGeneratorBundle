<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Channel and channel dependencies fixtures generator.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelGenerator implements GeneratorInterface
{
    const CHANNEL_FILENAME = 'channels.yml';

    const CURRENCY_FILENAME = 'currencies.yml';

    const DEFAULT_TREE = 'master';

    /** @var string */
    protected $channelsFilePath;

    /** @var string */
    protected $currenciesFilePath;

    /** @var Channel[] */
    protected $channels;

    /** @var Currency[] */
    protected $currencies;

    /** @var Locale[] */
    protected $locales;

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->channelsFilePath  = $globalConfig['output_dir'] . '/' . static::CHANNEL_FILENAME;
        $this->currenciesFilePath = $globalConfig['output_dir'] . '/' . static::CURRENCY_FILENAME;

        $this->locales = $this->generateLocales($config);

        $this->currencies = $this->generateCurrencies($config);

        $this->channels = $this->generateChannels($config);

        $this->writeCurrenciesFile();

        $this->writeChannelsFile($config);

        $progress->advance();

        return $this;
    }

    /**
     * Generate locales objects from channels configuration
     *
     * @param array $channelsConfig
     *
     * @return Locale[]
     */
    protected function generateLocales(array $channelsConfig)
    {
        $locales     = [];
        $localeCodes = [];

        foreach ($channelsConfig as $channelConfig) {
            $localeCodes = array_merge($localeCodes, $channelConfig['locales']);
        }

        $localeCodes = array_unique($localeCodes);

        foreach ($localeCodes as $localeCode) {
            $locale = new Locale();
            $locale->setCode($localeCode);

            $locales[$localeCode] = $locale;
        }

        return $locales;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function getCurrencies()
    {
        return $this->currencies;
    }

    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Generate currencies objects from channels configuration
     *
     * @param array $channelsConfig
     *
     * @return Currency[]
     */
    protected function generateCurrencies(array $channelsConfig)
    {
        $currencies    = [];
        $currencyCodes = [];

        foreach ($channelsConfig as $channelConfig) {
            $currencyCodes = array_merge($currencyCodes, $channelConfig['currencies']);
        }

        $currencyCodes = array_unique($currencyCodes);

        foreach ($currencyCodes as $currencyCode) {
            $currency = new Currency();
            $currency->setCode($currencyCode);
            $currency->setActivated(true);

            $currencies[$currencyCode] = $currency;
        }

        return $currencies;
    }

    /**
     * Generate channels objects from channels configuration
     *
     * @param array $channelsConfig
     *
     * @return Channel[]
     */
    protected function generateChannels(array $channelsConfig)
    {
        $channels = [];

        foreach ($channelsConfig as $channelConfig) {
            $channel = new Channel();

            $channel->setCode($channelConfig['code']);
            $channel->setLabel($channelConfig['label']);
            $channel->setColor($channelConfig['color']);

            foreach ($channelConfig['locales'] as $localeCode) {
                $locale = $this->locales[$localeCode];
                $channel->addLocale($locale);
            }

            foreach ($channelConfig['currencies'] as $currencyCode) {
                $currency = $this->currencies[$currencyCode];
                $channel->addCurrency($currency);
            }

            $channels[] = $channel;
        }


        return $channels;
    }

    /**
     * Write the currencies fixture file
     */
    protected function writeCurrenciesFile()
    {
        $currencyData = [ 'currencies' => [] ];
        foreach ($this->currencies as $currency) {
            $currencyData['currencies'][] = $currency->getCode();
        }
        $currencyData["removed_currencies"] = [];

        $yamlDumper = new Yaml\Dumper();
        $yamlCurrencies = $yamlDumper->dump($currencyData, 5, 0, true, true);

        file_put_contents($this->currenciesFilePath, $yamlCurrencies);
    }

    /**
     * Write the channels fixture file
     */
    protected function writeChannelsFile()
    {
        $channelData = [ 'channels' => [] ];
        foreach ($this->channels as $channel) {
            $localeCodes   = [];
            $currencyCodes = [];

            foreach ($channel->getLocales() as $locale) {
                $localeCodes[] = $locale->getCode();
            }

            foreach ($channel->getCurrencies() as $currency) {
                $currencyCodes[] = $currency->getCode();
            }

            $channelData['channels'][$channel->getCode()] =
                [
                    'code'       => $channel->getCode(),
                    'label'      => $channel->getLabel(),
                    'tree'       => static::DEFAULT_TREE,
                    'locales'    => $localeCodes,
                    'currencies' => $currencyCodes,
                    'color'      => $channel->getColor()
                ];
        }

        $yamlDumper = new Yaml\Dumper();
        $yamlChannels = $yamlDumper->dump($channelData, 5, 0, true, true);

        file_put_contents($this->channelsFilePath, $yamlChannels);
    }
}
