<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native CSV file for locales accesses. It gives all rights for every group in every locale.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalesAccessGenerator implements GeneratorInterface
{
    const LOCALE_ACCESSES_FILENAME = 'locale_accesses.csv';

    const LOCALE_ACCESSES = 'locale_accesses';

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
        $groups  = $options['groups'];
        $locales = $options['locales'];

        $groupNames = [];
        /** @var Group $group */
        foreach ($groups as $group) {
            if (User::GROUP_DEFAULT !== $group->getName()) {
                $groupNames[] = $group->getName();
            }
        }

        $data = [];
        /** @var LocaleInterface $locale */
        foreach ($locales as $locale) {
            $data[] = [
                'locale' => $locale->getCode(),
                'view_products' => implode(',', $groupNames),
                'edit_products' => implode(',', $groupNames),
            ];
        }
        $progress->advance();

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::LOCALE_ACCESSES_FILENAME
            ))
            ->write($data);
    }
}
