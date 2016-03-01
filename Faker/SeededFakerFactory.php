<?php

namespace Pim\Bundle\DataGeneratorBundle\Faker;

use Faker;

/**
 * Faker factory seeded from outside
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SeededFakerFactory implements FakerFactoryInterface
{
    /** @var string */
    protected $seed;

    /**
     * @{inheritdoc}
     */
    public function create()
    {
        if (null === $this->seed) {
            throw new \LogicException("The seeded faker factory has not been seeded !");
        }

        $faker = Faker\Factory::create();

        $faker->seed($this->seed);

        return $faker;
    }

    /**
     * Set the seed for the RNG
     *
     * @param string $seed
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;
    }
}
