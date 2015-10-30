<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\DataGeneratorBundle\Model\CategoryTree;
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

    /** @var  CategoryTree */
    protected $categoryTree;

    /** @var string */
    protected $outputFile;

    /** @var string */
    protected $delimiter;

    /** @var  int */
    protected $levelMax;

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
        $this->levelMax = (int)$config['levels'];

        $this->faker = Faker\Factory::create();

        $headers = ['code', 'parent'];
        foreach ($this->getLocales() as $locale) {
            $headers[] = 'label-'.$locale->getCode();
        }

        $currentLevel = 0;

        $this->categoryTree = new CategoryTree('master', 0);

        foreach ($this->getLocales() as $locale) {
            $this->categoryTree->addLabel($locale->getCode(), 'Master Catalog');
        }

        $this->feedTree($this->categoryTree, $currentLevel + 1, $count, $progress);

        $this->writeCsvFile($headers);

        return $this;
    }

    protected function feedTree(CategoryTree $categoryTree, $currentLevel, $count, ProgressHelper $progress)
    {
        for ($i = 0; $i < $count; $i++) {
            $categoryCode = self::CATEGORIES_CODE_PREFIX.uniqid();
            $categoryLeaf = new CategoryTree($categoryCode, $currentLevel);
            foreach ($this->getLocalizedRandomLabels() as $localeCode => $localeLabel) {
                $categoryLeaf->addLabel($localeCode, $localeLabel);
            }
            if ($currentLevel < $this->levelMax) {
                $this->feedTree($categoryLeaf, $currentLevel + 1, 2, $progress);
            }
            $categoryTree->addChild($categoryLeaf);
        }

        return $categoryTree;
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
     * Write the CSV file
     *
     * @param array $headers
     */
    protected function writeCsvFile(array $headers)
    {
        $csvFile = fopen($this->outputFile, 'w');
        fputcsv($csvFile, $headers, $this->delimiter);
        $lines = $this->flattenTree($this->categoryTree);
        foreach ($lines as $category) {
            fputcsv($csvFile, $category, $this->delimiter);
        }
        fclose($csvFile);
    }

    protected function flattenTree(CategoryTree $categoryTree, array $lines = [], CategoryTree $parent = null)
    {
        $flatCategory = $categoryTree->flatten();

        if ($parent) {
            $flatCategory['parent'] = $parent->getCode();
        }
        $lines [] = $flatCategory;
        foreach ($categoryTree->getChildren() as $child) {
            $lines = $this->flattenTree($child, $lines, $categoryTree);
        }

        return $lines;
    }
}
