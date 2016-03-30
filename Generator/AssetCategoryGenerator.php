<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker\Factory;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
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
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->locales = $options['locales'];

        $faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $faker->seed($globalConfig['seed']);
        }

        $assetCategories = [['code' => self::ASSET_MAIN_CATALOG, 'parent' => '']];

        /** @var Locale $locale */
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

        return [ self::ASSET_MAIN_CATALOG ];
    }
}
