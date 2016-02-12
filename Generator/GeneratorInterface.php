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
     * @param array          $config
     * @param string         $outputDir
     * @param ProgressHelper $progress
     * @param array          $options
     *
     * @return $this
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = []);
}
