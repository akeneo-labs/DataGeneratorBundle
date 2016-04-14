<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker\Factory;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Symfony\Component\Console\Helper\ProgressBar;

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
    const TYPE = 'asset_categories';

    const ASSET_CATEGORIES_FILENAME = 'asset_categories.csv';

    const ASSET_MAIN_CATALOG = 'asset_main_catalog';

    /** @var CsvWriter */
    protected $writer;

    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /** @var Locale[] */
    protected $locales;

    /**
     * Returns the codes of the generated asset categories.
     *
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $this->locales = $options['locales'];

        $faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $faker->seed($globalConfig['seed']);
        }

        $assetCategories = [['code' => self::ASSET_MAIN_CATALOG, 'parent' => '']];

        foreach ($this->locales as $locale) {
            $key = sprintf('label-%s', $locale->getCode());
            $assetCategories[0][$key] = implode(' ', $faker->words(3));
        }

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::ASSET_CATEGORIES_FILENAME
            ))
            ->write($assetCategories);

        $progress->advance();

        return ['asset_category_codes' => [self::ASSET_MAIN_CATALOG]];
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

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE == $type;
    }
}
