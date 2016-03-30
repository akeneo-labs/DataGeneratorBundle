<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
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
    const CHANNEL_FILENAME = 'channels.csv';

    const CURRENCY_FILENAME = 'currencies.csv';

    const DEFAULT_TREE = 'master';

    /** @var CsvWriter */
    protected $writer;

    /** @var string */
    protected $channelsFilePath;

    /** @var string */
    protected $currenciesFilePath;

    /** @var ChannelInterface[] */
    protected $channels = [];

    /** @var CurrencyInterface[] */
    protected $currencies = [];

    /** @var LocaleInterface[] */
    protected $locales = [];

    /**
     * @param CsvWriter $writer
     */
    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->channelsFilePath = sprintf(
            '%s%s%s',
            $globalConfig['output_dir'],
            DIRECTORY_SEPARATOR,
            self::CHANNEL_FILENAME
        );
        $this->currenciesFilePath = sprintf(
            '%s%s%s',
            $globalConfig['output_dir'],
            DIRECTORY_SEPARATOR,
            self::CURRENCY_FILENAME
        );

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
     * @return LocaleInterface[]
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

    /**
     * @return LocaleInterface[]
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @return CurrencyInterface[]
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @return ChannelInterface[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Generate currencies objects from channels configuration
     *
     * @param array $channelsConfig
     *
     * @return CurrencyInterface[]
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
     * @return ChannelInterface[]
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
        $data = [];
        foreach ($this->currencies as $currency) {
            $data[] = [
                'code'      => $currency->getCode(),
                'activated' => 1
            ];
        }

        $this->writer
            ->setFilename($this->currenciesFilePath)
            ->write($data);
    }

    /**
     * Write the channels fixture file
     */
    protected function writeChannelsFile()
    {
        $data = [];
        foreach ($this->channels as $channel) {
            $localeCodes   = [];
            $currencyCodes = [];

            foreach ($channel->getLocales() as $locale) {
                $localeCodes[] = $locale->getCode();
            }

            foreach ($channel->getCurrencies() as $currency) {
                $currencyCodes[] = $currency->getCode();
            }

            $data[] = [
                'code'       => $channel->getCode(),
                'label'      => $channel->getLabel(),
                'tree'       => self::DEFAULT_TREE,
                'locales'    => implode(',', $localeCodes),
                'currencies' => implode(',', $currencyCodes),
            ];
        }

        $this->writer
            ->setFilename($this->channelsFilePath)
            ->write($data);
    }
}
