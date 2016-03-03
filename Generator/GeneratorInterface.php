<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\ProgressBar;

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
     * @param array          $generatorConfig
     * @param ProgressBar $progress
     * @param array          $options
     *
     * @return $this
     */
    public function generate(array $globalConfig, array $generatorConfig, ProgressBar $progress, array $options = []);
}
