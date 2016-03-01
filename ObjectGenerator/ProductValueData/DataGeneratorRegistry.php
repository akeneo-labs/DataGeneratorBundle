<?php

namespace Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData;

use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Register product value data generator and provides the one
 * supported the provided Attribute
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataGeneratorRegistry
{
    /** @var DataGenerator[] */
    protected $dataGenerators = [];

    /**
     * Register a data generator
     *
     * @param DataGeneratorInterface $dataGenerator
     */
    public function register(DataGeneratorInterface $dataGenerator)
    {
        $this->dataGenerators[] = $dataGenerator;
    }

    /**
     * Get the first data generator supporting the attribute
     *
     * @param AttributeInterface $attribute
     */
    public function getDataGenerator(AttributeInterface $attribute)
    {
        foreach($this->dataGenerators as $dataGenerator) {
            if ($dataGenerator->supportsGeneration($attribute)) {
                return $dataGenerator;
            }
        }

        return null;
    }
}
