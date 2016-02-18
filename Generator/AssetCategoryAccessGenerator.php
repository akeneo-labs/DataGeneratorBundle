<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for asset categories accesses. It gives all rights for every group in every category.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCategoryAccessGenerator implements GeneratorInterface
{
    const ASSET_CATEGORY_ACCESSES_FILENAME = 'asset_category_accesses.yml';

    const ASSET_CATEGORY_ACCESSES = 'asset_category_accesses';

    /** @var Group[] */
    protected $groups;

    /** @var string[] */
    protected $assetCategoryCodes;

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->groups             = $options['groups'];
        $this->assetCategoryCodes = $options['asset_category_codes'];

        $data = [];
        foreach ($this->assetCategoryCodes as $assetCategoryCode) {
            $data[$assetCategoryCode] = [];
            foreach (['viewItems', 'editItems'] as $access) {
                $data[$assetCategoryCode][$access] = [];
                foreach ($this->groups as $group) {
                    if ('all' !== $group->getName()) {
                        $data[$assetCategoryCode][$access][] = $group->getName();
                    }
                }
            }
        }

        $assetCategoryAccesses = [self::ASSET_CATEGORY_ACCESSES => $data];

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

        file_put_contents($outputDir.'/'.self::ASSET_CATEGORY_ACCESSES_FILENAME, $yamlData);
    }
}
