<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
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
    const TYPE = 'asset_category_accesses';

    const ASSET_CATEGORY_ACCESSES_FILENAME = 'asset_category_accesses.yml';

    /** @var CsvWriter */
    protected $writer;

    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressHelper $progress, array $options = [])
    {
        $groups             = $options['user_groups'];
        $assetCategoryCodes = $options['asset_category_codes'];

        $data = [];
        $groupNames = [];
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
