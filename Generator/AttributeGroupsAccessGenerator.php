<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for attribute groups accesses. It gives all rights for every group in every attribute
 * group.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupsAccessGenerator implements GeneratorInterface
{
    const ASSET_CATEGORY_ACCESSES_FILENAME = 'attribute_groups_accesses.yml';

    const ATTRIBUTE_GROUPS_ACCESSES = 'attribute_groups_accesses';

    /** @var Group[] */
    protected $groups;

    /** @var AttributeGroup[] */
    protected $attributeGroups;

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressBar $progress, array $options = [])
    {
        $this->groups          = $options['groups'];
        $this->attributeGroups = $options['attribute_groups'];

        $data = [];
        foreach ($this->attributeGroups as $attributeGroup) {
            $attributeGroupCode = $attributeGroup->getCode();
            $data[$attributeGroupCode] = [];
            foreach (['viewAttributes', 'editAttributes'] as $access) {
                $data[$attributeGroupCode][$access] = [];
                foreach ($this->groups as $group) {
                    if ('all' !== $group->getName()) {
                        $data[$attributeGroupCode][$access][] = $group->getName();
                    }
                }
            }
        }

        $attributeGroupsAccesses = [self::ATTRIBUTE_GROUPS_ACCESSES => $data];

        $progress->advance();

        $this->writeYamlFile($attributeGroupsAccesses, $globalConfig['output_dir']);
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
