<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

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
     * @param int   $amount
     * @param array $options
     *
     * @return $this
     */
    public function generate($amount, array $options);
}
