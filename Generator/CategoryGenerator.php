<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate categories fixtures
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryGenerator
{
    const CATEGORIES_FILENAME = 'categories.csv';
    const CATEGORIES_CODE_PREFIX = 'cat_';
    const LABEL_LENGTH = 2;

    /** @var Locale[] */
    protected $locales;

    /** @var Faker\Generator */
    protected $faker;

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = [])
    {
        $this->locales = $options['locales'];

        $this->faker = Faker\Factory::create();

        $delimiter = $config['delimiter'];
        $count     = (int)$config['count'];
        $levelMax  = (int)$config['levels'];

        $countByLevel = $this->calculateNodeCountPerLevel($levelMax, $count);

        $headers = ['code', 'parent'];
        foreach ($this->locales as $locale) {
            $headers[] = 'label-'.$locale->getCode();
        }

        $rootCategory = $this->generateCategory('master', 'Master Catalog');
        $categories = $this->generateCategories($rootCategory, 1, $countByLevel, $levelMax);

        $normalizedCategories = $this->normalizeCategories($categories);

        $outputFile = $outputDir.'/'.self::CATEGORIES_FILENAME;
        $this->writeCsvFile($headers, $normalizedCategories, $outputFile, $delimiter);

        $progress->advance($count);

        return $this->flattenCategories($categories);
    }

    /**
     * Generate categories in a tree structure
     *
     * @param Category $parent
     * @param int $level
     * @param int $count
     * @param int $levelMax
     *
     * @return Category
     */
    protected function generateCategories(Category $parent, $level, $count, $levelMax)
    {
        for ($i = 0; $i < $count; $i++) {
            $categoryCode = $parent->getCode().'_'.$i;

            $category = $this->generateCategory($categoryCode);

            if ($level < $levelMax) {
                $this->generateCategories($category, $level + 1, $count, $levelMax);
            }

            $parent->addChild($category);
        }

        return $parent;
    }

    /**
     * Generate a category object
     *
     * @param string $code
     * @param string $forcedLabel
     *
     * @return Category $category
     */
    protected function generateCategory($code, $forcedLabel = null)
    {
        $category = new Category();
        $category->setCode($code);

        foreach ($this->locales as $locale) {
            $translation = new CategoryTranslation();
            $translation->setLocale($locale);

            if (null === $forcedLabel) {
                $translation->setLabel($this->faker->sentence(static::LABEL_LENGTH));
            } else {
                $translation->setLabel($forcedLabel);
            }
            $category->addTranslation($translation);
        }

        return $category;
    }

    /**
     * Normalize Categories objects into a flat array
     *
     * @param Category $category
     * @param array    $normalizedCategories
     *
     * @return array
     */
    protected function normalizeCategories(Category $category, array $normalizedCategories = [])
    {
        $normalizedCategory = $this->normalizeCategory($category);

        $normalizedCategories[] = $normalizedCategory;

        foreach ($category->getChildren() as $child) {
            $normalizedCategories = $this->normalizeCategories($child, $normalizedCategories);
        }

        return $normalizedCategories;
    }

    /**
     * Normalize a category object into a flat array
     *
     * @param Category $category
     *
     * @return array
     */
    protected function normalizeCategory(Category $category)
    {
        $normalizedCategory = [
            'code'   => $category->getCode()
        ];

        if (null !== $category->getParent()) {
            $normalizedCategory['parent'] = $category->getParent()->getCode();
        } else {
            $normalizedCategory['parent'] = "";
        }

        foreach ($category->getTranslations() as $translation) {
            $labelCode = 'label-'.$translation->getLocale()->getCode();

            $normalizedCategory[$labelCode] = $translation->getLabel();
        }

        return $normalizedCategory;
    }

    /**
     * Flatten the category tree into an array
     *
     * @param Category $category
     * @param array    $flatCategories
     *
     * @return array
     */
    protected function flattenCategories(Category $category, array $flatCategories = [])
    {
        $flatCategories[$category->getCode()] = $category;

        foreach ($category->getChildren() as $child) {
            $flatCategories = $this->flattenCategories($child, $flatCategories);
        }

        return $flatCategories;
    }

    /**
     * Write the CSV file
     */
    protected function writeCsvFile(array $headers, array $normalizedCategories, $outputFile, $delimiter)
    {
        $csvFile = fopen($outputFile, 'w');

        fputcsv($csvFile, $headers, $delimiter);

        foreach ($normalizedCategories as $category) {
            fputcsv($csvFile, $category, $delimiter);
        }
        fclose($csvFile);
    }


    /**
     * Calculate on approximation for the average number of nodes per level needed from the
     * provided node count and level count
     *
     * @param int $levelCount
     * @param int $nodeCount
     *
     * @return int
     */
    protected function calculateNodeCountPerLevel($levelCount, $nodeCount)
    {
        $lowerLimit = 1;

        $upperLimit = round(pow($nodeCount, 1/$levelCount));

        $approximationFound = false;
        $avgNodeCount = $lowerLimit;

        $prevDistance = PHP_INT_MAX;
        $prevAvgNodeCount = null;

        while (!$approximationFound && $avgNodeCount < $upperLimit) {

            $distance = abs($nodeCount - $this->calculateTotalNodesNumber($levelCount, $avgNodeCount));

            if ($distance > $prevDistance) {
                $approximationFound = true;

            } else {
                $previousDistance = $distance;
                $prevAvgNodeCount = $avgNodeCount;
                $avgNodeCount++;
            }
        }

        return $prevAvgNodeCount;
    }

    /**
     * Get the total number of nodes based on levels count and average node count
     * per level
     *
     * @param int $levelCount
     * @param int $avgNodeCount
     *
     * @return int
     */
    protected function calculateTotalNodesNumber($levelCount, $avgNodeCount)
    {
        $totalNodeCount = 0;

        for ($level = 1; $level <= $levelCount; $level++) {
            $totalNodeCount += pow($avgNodeCount, $level);
        }

        return $totalNodeCount;
    }
}
