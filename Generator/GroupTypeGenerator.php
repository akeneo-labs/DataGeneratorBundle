<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for group types. No configuration allowed, it generates VARIANT and RELATED group types.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeGenerator implements GeneratorInterface
{
    const GROUP_TYPES_FILENAME = 'group_types.yml';

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressBar $progress, array $options = [])
    {
        $variantGroupType = new GroupType();
        $variantGroupType->setVariant(true);
        $variantGroupType->setCode('VARIANT');

        $relatedGroupType = new GroupType();
        $relatedGroupType->setVariant(false);
        $relatedGroupType->setCode('RELATED');

        $groupTypes = [$variantGroupType, $relatedGroupType];

        $data = ['group_types' => []];

        foreach ($groupTypes as $groupType) {
            $data['group_types'] = array_merge($data['group_types'], $this->normalizeGroupType($groupType));
        }

        $progress->advance();

        $this->writeYamlFile($data, $globalConfig['output_dir']);

        return $groupTypes;
    }

    /**
     * @param GroupTypeInterface $groupType
     *
     * @return array
     */
    public function normalizeGroupType(GroupTypeInterface $groupType)
    {
        return [
            $groupType->getCode() => [
                'variant' => $groupType->isVariant() ? 1 : 0
            ]
        ];
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

        file_put_contents($outputDir.'/'.self::GROUP_TYPES_FILENAME, $yamlData);
    }
}
