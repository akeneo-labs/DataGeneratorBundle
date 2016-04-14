<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Bundle\UserBundle\Entity\User;
use Symfony\Component\Console\Helper\ProgressBar;
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
    const TYPE = 'product_category_accesses';

    const PRODUCT_CATEGORY_ACCESSES_FILENAME = 'product_category_accesses.csv';

    const PRODUCT_CATEGORY_ACCESSES = 'product_category_accesses';

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
        $groups     = $options['user_groups'];
        $categories = $options['categories'];

        $groupNames = [];
        foreach ($groups as $group) {
            if (User::GROUP_DEFAULT !== $group->getName()) {
                $groupNames[] = $group->getName();
            }
        }

        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'category'   => $category->getCode(),
                'view_items' => implode(',', $groupNames),
                'edit_items' => implode(',', $groupNames),
                'own_items'  => implode(',', $groupNames),
            ];
        }
        $progress->advance();

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::PRODUCT_CATEGORY_ACCESSES_FILENAME
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
