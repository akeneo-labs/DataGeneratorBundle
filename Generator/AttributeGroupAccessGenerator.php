<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Bundle\UserBundle\Entity\User;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml;

/**
 * Generate native CSV file for attribute groups accesses. It gives all rights for every group in every attribute
 * group.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupAccessGenerator implements GeneratorInterface
{
    const TYPE = 'attribute_group_accesses';

    const ASSET_CATEGORY_ACCESSES_FILENAME = 'attribute_group_accesses.csv';

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
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $groups          = $options['user_groups'];
        $attributeGroups = $options['attribute_groups'];

        $data = [];
        $groupNames = [];
        foreach ($groups as $group) {
            if (User::GROUP_DEFAULT !== $group->getName()) {
                $groupNames[] = $group->getName();
            }
        }

        foreach ($attributeGroups as $attributeGroup) {
            $assetCategoryAccess = ['attribute_group' => $attributeGroup->getCode()];
            foreach (['view_attributes', 'edit_attributes'] as $access) {
                $assetCategoryAccess[$access] = implode(',', $groupNames);
            }
            $data[] = $assetCategoryAccess;
        }
        $progress->advance();

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::ASSET_CATEGORY_ACCESSES_FILENAME
            ))
            ->write($data);

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE == $type;
    }
}
