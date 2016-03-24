<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native CSV file for group types. No configuration allowed, it generates VARIANT and RELATED group types.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeGenerator implements GeneratorInterface
{
    const GROUP_TYPES_FILENAME = 'group_types.csv';

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $variantGroupType = new GroupType();
        $variantGroupType->setVariant(true);
        $variantGroupType->setCode('VARIANT');

        $relatedGroupType = new GroupType();
        $relatedGroupType->setVariant(false);
        $relatedGroupType->setCode('RELATED');

        $groupTypes = [$variantGroupType, $relatedGroupType];

        $data = [];
        foreach ($groupTypes as $groupType) {
            $data[] = $this->normalizeGroupType($groupType);
        }

        $progress->advance();

        $csvWriter = new CsvWriter($globalConfig['output_dir'] . '/' . self::GROUP_TYPES_FILENAME, $data);
        $csvWriter->write();

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
            'code'       => $groupType->getCode(),
            'is_variant' => $groupType->isVariant() ? 1 : 0
        ];
    }
}
