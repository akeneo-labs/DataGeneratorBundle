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
 * Generate native YML file for currencies
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyGenerator implements GeneratorInterface
{
    const CURRENCIES_FILENAME='currencies.yml';

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress)
    {
        $currenciesFile = $outputDir.'/'.self::CURRENCIES_FILENAME;

        $count = (int) $config['count'];
        $forcedCurrencies = $config['force_currencies'];

        $pimFaker = PimFactory::create();

        $currencies = $pimFaker->currencies($count, $forcedCurrencies);

        $normalizedCurrencies = ['currencies' => []];

        foreach ($currencies as $currency) {
            $normalizedCurrencies['currencies'][] = $currency->getCode();
        }

        $this->writeYamlFile($normalizedCurrencies, $currenciesFile);

        return $currencies;
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
