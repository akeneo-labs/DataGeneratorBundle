<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generates CSV locales file for fixtures.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleGenerator implements GeneratorInterface
{
    const LOCALES_FILENAME = 'locales.csv';

    const INTERNAL_LOCALES_FILE = 'Resources/config/locales.csv';

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $generatorConfig, ProgressHelper $progress, array $options = [])
    {
        copy(
            dirname(__FILE__) . '/../' . self::INTERNAL_LOCALES_FILE,
            $globalConfig['output_dir'] . '/' . self::LOCALES_FILENAME
        );

        $progress->advance();
    }
}
