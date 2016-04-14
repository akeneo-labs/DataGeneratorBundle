<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Data generator interface
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GeneratorInterface
{
    /**
     * Generate the amount of entity
     *
     * @param array          $globalConfig
     * @param array          $entitiesConfig
     * @param ProgressHelper $progress
     * @param array          $options
     *
     * @return array
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressHelper $progress, array $options = []);

    /**
     * Check if the Generator can generate type
     *
     * @param string $type
     *
     * @return bool
     */
    public function supports($type);
}
