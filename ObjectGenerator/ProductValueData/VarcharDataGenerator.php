<?php

namespace Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData;

use Faker;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Generate varchar data for a product value
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VarcharDataGenerator implements DataGeneratorInterface
{
    /** @staticvar */
    const WORDS_COUNT = 3;

    /**
     * @{inheritdoc}
     */
    public function generateData(AttributeInterface $attribute)
    {
        $faker = Faker\Factory::create();

        if ('url' === $attribute->getValidationRule()) {
            $data = $faker->url();
        } else {
            $data = $faker->words(static::WORDS_COUNT, true);
        }

        return $data;
    }

    /**
     * @{inheritdoc}
     */
    public function supportsGeneration(AttributeInterface $attribute)
    {
        return ('varchar' === $attribute->getBackendType());
    }
}
