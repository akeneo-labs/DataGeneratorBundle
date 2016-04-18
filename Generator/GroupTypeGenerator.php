<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Symfony\Component\Console\Helper\ProgressBar;
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
    const TYPE = 'group_types';

    const GROUP_TYPES_FILENAME = 'group_types.csv';

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

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::GROUP_TYPES_FILENAME
            ))
            ->write($data);

        return ['group_types' => $groupTypes];
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

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE === $type;
    }
}
