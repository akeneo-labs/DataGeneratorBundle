<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\DataGeneratorBundle\Writer\WriterInterface;
use Pim\Bundle\UserBundle\Entity\User;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generate native CSV file for asset categories accesses. It gives all rights for every group in every category.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCategoryAccessGenerator implements GeneratorInterface
{
    const ASSET_CATEGORY_ACCESSES_FILENAME = 'asset_category_accesses.csv';

    /** @var WriterInterface */
    protected $writer;

    public function __construct(WriterInterface $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $groups             = $options['groups'];
        $assetCategoryCodes = $options['asset_category_codes'];

        $data = [];
        $groupNames = [];
        /** @var Group $group */
        foreach ($groups as $group) {
            if (User::GROUP_DEFAULT !== $group->getName()) {
                $groupNames[] = $group->getName();
            }
        }

        foreach ($assetCategoryCodes as $assetCategoryCode) {
            $assetCategoryAccess = ['category' => $assetCategoryCode];
            foreach (['view_items', 'edit_items'] as $access) {
                $assetCategoryAccess[$access] = implode(',', $groupNames);
            }
            $data[] = $assetCategoryAccess;
        }
        $progress->advance();

        $this->writer
            ->setFilename($globalConfig['output_dir'] . '/' . self::ASSET_CATEGORY_ACCESSES_FILENAME)
            ->setData($data)
            ->write();
    }
}
