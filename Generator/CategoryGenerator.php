<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate categories fixtures
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryGenerator implements GeneratorInterface
{
    const CATEGORIES_FILENAME = 'categories.csv';
    const CATEGORIES_CODE_PREFIX = 'cat_';
    const DEFAULT_DELIMITER = ';';

    /** @var Locale[] */
    protected $locales;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var Faker\Generator */
    protected $faker;

    /** @var array */
    protected $categories;

    /** @var string */
    protected $outputFile;

    /** @var string */
    protected $delimiter;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(LocaleRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $this->outputFile = $outputDir.'/'.self::CATEGORIES_FILENAME;
        $delimiter = $config['delimiter'];
        $this->delimiter = ($delimiter != null) ? $delimiter : self::DEFAULT_DELIMITER;

        $count = (int)$config['count'];

        $this->faker = Faker\Factory::create();

        $this->categories = [];

        $this->categories[] = [
            'code'   => 'master',
            'parent' => '',
            'label-en_US' => 'Master Catalog',
        ];

        $headers = ['code', 'parent'];
        foreach ($this->getLocales() as $locale) {
            $headers[] = 'label-'.$locale->getCode();
        }

        for ($i = 0; $i < $count; $i++) {
            $category = [];
            $category['code'] = self::CATEGORIES_CODE_PREFIX.$i;
            $category['parent'] = 'master';
            foreach($this->getLocalizedRandomLabels() as $localeCode => $localeLabel) {
                $category[$localeCode] = $localeLabel;
            }
            $this->categories[] = $category;
            $progress->advance();
        }

        $this->writeCsvFile($this->categories, $headers);

        return $this;
    }

    /**
     * Get localized random labels
     *
     * @return array
     */
    protected function getLocalizedRandomLabels()
    {
        $locales = $this->getLocales();
        $labels = [];

        foreach ($locales as $locale) {
            $labels[$locale->getCode()] = $this->faker->sentence(2);
        }

        return $labels;
    }

    /**
     * Get active locales
     *
     * @return Locale[]
     */
    protected function getLocales()
    {
        if (null === $this->locales) {
            $this->locales = [];
            /** @var Locale[] $locales */
            $locales = $this->localeRepository->findBy(['activated' => 1]);
            foreach ($locales as $locale) {
                $this->locales[$locale->getCode()] = $locale;
            }
        }

        return $this->locales;
    }

    /**
     * Write the CSV file from products and headers
     *
     * @param array $categories
     * @param array $headers
     *
     * @internal param array $products
     */
    protected function writeCsvFile(array $categories, array $headers)
    {
        $csvFile = fopen($this->outputFile, 'w');

        fputcsv($csvFile, $headers, $this->delimiter);

        foreach ($categories as $category) {
            fputcsv($csvFile, $category, $this->delimiter);
        }
        fclose($csvFile);
    }
}
