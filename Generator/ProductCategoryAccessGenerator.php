<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\User;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native CSV file for product categories accesses. It gives all rights for every group in every category.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryAccessGenerator implements GeneratorInterface
{
    const PRODUCT_CATEGORY_ACCESSES_FILENAME = 'product_category_accesses.csv';

    const PRODUCT_CATEGORY_ACCESSES = 'product_category_accesses';

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $groups     = $options['groups'];
        $categories = $options['categories'];

        $groupNames = [];
        /** @var Group $group */
        foreach ($groups as $group) {
            if (User::GROUP_DEFAULT !== $group->getName()) {
                $groupNames[] = $group->getName();
            }
        }

        $data = [];
        /** @var CategoryInterface $category */
        foreach ($categories as $category) {
            $data[] = [
                'category'   => $category->getCode(),
                'view_items' => implode(',', $groupNames),
                'edit_items' => implode(',', $groupNames),
                'own_items'  => implode(',', $groupNames),
            ];
        }
        $progress->advance();

        $csvWriter = new CsvWriter($globalConfig['output_dir'] . '/' . self::PRODUCT_CATEGORY_ACCESSES_FILENAME, $data);
        $csvWriter->write();
    }
}
