<?php

namespace Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData;

use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Interface for generator of product value data
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DataGeneratorInterface
{
    /**
     * Generate a fake random product value data
     *
     * @param AttributeInterface $attribute
     *
     * @return mixed
     */
    public function generateData(AttributeInterface $attribute);

    /**
     * Tells if the provided backend is supported by this generator
     *
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    public function supportsGeneration(AttributeInterface $attribute);
}
