<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;
use Pim\Bundle\DataGeneratorBundle\Faker\PimFactory;

/**
 * Generate native YML file for channel
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelGenerator implements GeneratorInterface
{
    const CHANNELS_FILENAME='channels.yml';

    /** @var array */
    protected $currencies;

    /**
     * @param array $currencies
     */
    public function setCurrencies(array $currencies)
    {
        $this->currencies = $currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress)
    {
        $channelsFile = $outputDir.'/'.self::CHANNELS_FILENAME;

        $count = (int) $config['count'];
        $forcedChannels = $config['force_channels'];

        $pimFaker = PimFactory::create();

        $channels = $pimFaker->channels($count, $forcedChannels, null, $this->currencies);

        $normalizedChannels = [ 'channels' => [] ];

        foreach ($channels as $channel) {
            $normalizedChannel = [
                'code'       => $channel->getCode(),
                'label'      => $channel->getLabel(),
                'tree'       => "TODO",
                'locales'    => [],
                'currencies' => [],
                'color'      => $channel->getColor()
            ];

            foreach ($channel->getLocales() as $locale) {
                $normalizedChannel['locales'][] = $locale->getCode();
            }

            foreach ($channel->getCurrencies() as $currency) {
                $normalizedChannel['currencies'][] = $currency->getCode();
            }

            $normalizedChannels['channels'][$channel->getCode()] = $normalizedChannel;
        }

        $this->writeYamlFile($normalizedChannels, $channelsFile);

        return $channels;
    }


    /**
     * Write a YAML file
     *
     * @param array  $data
     * @param string $filename
     */
    protected function writeYamlFile(array $data, $filename)
    {
        $dumper = new Yaml\Dumper();
        $yamlData = $dumper->dump($data, 5, 0, true, true);

        file_put_contents($filename, $yamlData);
    }
}
