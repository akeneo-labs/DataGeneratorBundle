<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native CSV file for attribute groups accesses. It gives all rights for every group in every attribute
 * group.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupsAccessGenerator implements GeneratorInterface
{
    const ASSET_CATEGORY_ACCESSES_FILENAME = 'attribute_groups_accesses.csv';

    const ATTRIBUTE_GROUPS_ACCESSES = 'attribute_groups_accesses';

    /** @var CsvWriter */
    protected $writer;

    /**
     * @param CsvWriter $writer
     */
    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $groups          = $options['groups'];
        $attributeGroups = $options['attribute_groups'];

        $data = [];
        $groupNames = [];
        /** @var Group $group */
        foreach ($groups as $group) {
            if (User::GROUP_DEFAULT !== $group->getName()) {
                $groupNames[] = $group->getName();
            }
        }

        /** @var AttributeGroupInterface $attributeGroup */
        foreach ($attributeGroups as $attributeGroup) {
            $assetCategoryAccess = ['attribute_group' => $attributeGroup->getCode()];
            foreach (['view_attributes', 'edit_attributes'] as $access) {
                $assetCategoryAccess[$access] = implode(',', $groupNames);
            }
            $data[] = $assetCategoryAccess;
        }
        $progress->advance();

        $this->writer
            ->setFilename($globalConfig['output_dir'] . '/' . self::ASSET_CATEGORY_ACCESSES_FILENAME)
            ->write($data);
    }
}
