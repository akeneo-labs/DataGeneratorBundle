<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for product categories accesses. It gives all rights for every group in every category.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryAccessGenerator implements GeneratorInterface
{
    const PRODUCT_CATEGORY_ACCESSES_FILENAME = 'product_category_accesses.yml';

    const PRODUCT_CATEGORY_ACCESSES = 'product_category_accesses';

    /** @var Group[] */
    protected $groups;

    /** @var Category[] */
    protected $categories;

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->groups     = $options['groups'];
        $this->categories = $options['categories'];

        $data = [];
        foreach ($this->categories as $category) {
            $categoryCode = $category->getCode();
            $data[$categoryCode] = [];
            foreach (['viewItems', 'editItems', 'ownItems'] as $access) {
                $data[$categoryCode][$access] = [];
                foreach ($this->groups as $group) {
                    if ('all' !== $group->getName()) {
                        $data[$categoryCode][$access][] = $group->getName();
                    }
                }
            }
        }

        $assetCategoryAccesses = [self::PRODUCT_CATEGORY_ACCESSES => $data];

        $progress->advance();

        $this->writeYamlFile($assetCategoryAccesses, $globalConfig['output_dir']);
    }

    /**
     * Write a YAML file
     *
     * @param array  $data
     * @param string $outputDir
     */
    protected function writeYamlFile(array $data, $outputDir)
    {
        $dumper = new Yaml\Dumper();
        $yamlData = $dumper->dump($data, 3, 0, true, true);

        file_put_contents($outputDir.'/'.self::PRODUCT_CATEGORY_ACCESSES_FILENAME, $yamlData);
    }
}
