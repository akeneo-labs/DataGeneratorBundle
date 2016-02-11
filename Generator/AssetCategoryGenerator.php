<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generate native CSV file for asset categories
 *
 * Warning: for now, it only generates one asset category (it has no options).
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCategoryGenerator implements GeneratorInterface
{
    const ASSET_CATEGORIES_FILENAME = 'asset_categories.csv';

    const ASSET_MAIN_CATALOG = 'asset_main_catalog';

    /** @var Locale[] */
    protected $locales;

    /**
     * Returns the codes of the generated asset categories.
     *
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $this->locales = $options['locales'];

        $faker = Faker\Factory::create();

        $assetCategories = [['code' => self::ASSET_MAIN_CATALOG, 'parent' => '']];

        /** @var Locale $locale */
        foreach ($this->locales as $locale) {
            $key = sprintf('label-%s', $locale->getCode());
            $assetCategories[0][$key] = implode(' ', $faker->words(3));
        }

        $headers = array_keys($assetCategories[0]);

        $this->writeCsvFile($assetCategories, $headers, $outputDir);

        $progress->advance();

        return [ self::ASSET_MAIN_CATALOG ];
    }

    /**
     * Write the CSV file from products and headers
     *
     * @param array  $assetCategories
     * @param array  $headers
     * @param string $outputDir
     */
    protected function writeCsvFile(array $assetCategories, array $headers, $outputDir)
    {
        $csvFile = fopen($outputDir.'/'.self::ASSET_CATEGORIES_FILENAME, 'w');

        fputcsv($csvFile, $headers, ';');
        $headersAsKeys = array_fill_keys($headers, "");

        foreach ($assetCategories as $assetCategory) {
            $productData = array_merge($headersAsKeys, $assetCategory);
            fputcsv($csvFile, $productData, ';');
        }
        fclose($csvFile);
    }
}
