<?php

namespace Pim\Bundle\DataGeneratorBundle\Faker;

/**
 * Interface for a faker factory service
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FakerFactoryInterface
{
    /**
     * Create a Faker generator
     *
     * @return Faker\Generator;
     */
    public create();
}
